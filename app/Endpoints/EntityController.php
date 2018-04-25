<?php

namespace App\Controllers;

use App\Entity\
{
	AnnotationTerm, Atomic, AtomicState, Compartment, Complex, Entity, EntityAnnotation, EntityStatus, IdentifiedObject, Repositories\EntityRepository, Repositories\EntityRepositoryImpl, Repositories\IRepository, Structure
};
use App\Exceptions\
{
	InvalidArgumentException, InvalidEnumFieldValueException, MalformedInputException, NonExistingObjectException, UniqueKeyViolationException
};
use App\Helpers\ArgumentParser;
use App\Helpers\Validators;
use Consistence\Enum\InvalidEnumValueException;
use Slim\Http\{Request, Response};

/**
 * @property-read EntityRepository $repository
 * @method Entity getObject(int $id)
 */
final class EntityController extends WritableRepositoryController
{
	protected static function getAllowedSort(): array
	{
		return ['id', 'name', 'type', 'code'];
	}

	protected function getFilter(ArgumentParser $args): array
	{
		$filter = [];

		if ($args->hasKey('annotation'))
		{
			$parts = explode(':', $args->getString('annotation'));
			if (count($parts) !== 2)
				throw new InvalidArgumentException('annotation', $args->getString('annotation'), 'must be in format term:id');

			try {
				$term = AnnotationTerm::get(strtolower($parts[0]));
			}
			catch (InvalidEnumValueException $e) {
				throw new InvalidEnumFieldValueException('termType', $parts[0], implode(', ', AnnotationTerm::getAvailableValues()));
			}

			$filter['annotation'] = ['type' => $term, 'id' => $parts[1]];
		}
		elseif ($args->hasKey('name'))
			$filter['name'] = $args->getString('name');

		return $filter;
	}

	public function readCode(Request $request, Response $response, ArgumentParser $args)
	{
		$entity = $this->repository->getByCode($args->getString('code'));
		return self::formatOk(
			$response,
			$entity ? $this->getData($entity) : null
		);
	}

	public function editStatus(Request $request, Response $response, ArgumentParser $args)
	{
		$entity = $this->getObject($args->getInt('id'));
		$body = new ArgumentParser($request->getParsedBody());
		try {
			$status = EntityStatus::get($body->getString('status'));
			$entity->setStatus($status);
			$this->orm->persist($entity);
			$this->orm->flush();
		}
		catch (InvalidEnumValueException $e) {
			throw new InvalidArgumentException('status', $body->getString('status'), implode(', ', EntityStatus::getAvailableValues()));
		}

		return self::formatOk($response, null);
	}

	/**
	 * @param ArgumentParser $data
	 * @return Entity
	 * @throws InvalidArgumentException
	 */
	protected function createObject(ArgumentParser $data): IdentifiedObject
	{
		if (!$data->hasKey('type'))
			throw new InvalidArgumentException('type', null);

		$cls = array_search($type = $data->getString('type'), Entity::$classToType, true);
		if (!$cls || $cls == AtomicState::class)
			throw new InvalidArgumentException('type', $type);

		return new $cls;
	}

	/**
	 * @param Entity $entity
	 * @return array
	 */
	protected function getData($entity): array
	{
		return [
			'id' => $entity->getId(),
			'name' => $entity->getName(),
			'description' => $entity->getDescription(),
			'code' => $entity->getCode(),
			'type' => $entity->getType(),
			'status' => (string)$entity->getStatus(),
			'classifications' => $entity->getClassifications()->map(self::identifierGetter())->toArray(),
			'organisms' => $entity->getOrganisms()->map(self::identifierGetter())->toArray(),
			'annotations' => $entity->getAnnotations()->map(function(EntityAnnotation $annotation)
			{
				return ['id' => $annotation->getTermId(), 'type' => $annotation->getTermType()];
			})->toArray(),
		] + $this->getSpecificData($entity);
	}

	private function getSpecificData(Entity $entity): array
	{
		if ($entity instanceof Compartment)
		{
			return [
				'children' => $this->repository
					->findComplexChildren($entity)
					->map(self::identifierGetter())
					->toArray(),
				'parent' => (($parent = $entity->getParents()->first()) ? $parent->getId() : null)
			];
		}
		elseif ($entity instanceof Complex)
		{
			return [
				'compartments' => $entity->getCompartments()->map(self::identifierGetter())->toArray(),
				'children' => $entity->getChildren()->map(self::identifierGetter())->toArray(),
			];
		}
		elseif ($entity instanceof Structure)
		{
			return [
				'compartments' => $entity->getCompartments()->map(self::identifierGetter())->toArray(),
				'parents' => $entity->getParents()->map(self::identifierGetter())->toArray(),
				'children' => $entity->getChildren()->map(self::identifierGetter())->toArray(),
			];
		}
		elseif ($entity instanceof Atomic)
		{
			return [
				'compartments' => $entity->getCompartments()->map(self::identifierGetter())->toArray(),
				'parents' => $entity->getParents()->map(self::identifierGetter())->toArray(),
				'states' => $this->repository->findAtomicStates($entity)->map(function(AtomicState $state)
				{
					return ['code' => $state->getCode(), 'description' => $state->getDescription()];
				})->toArray(),
			];
		}
		else
			return [];
	}

	private function setStates(Atomic $entity, array $input)
	{
		$states = [];
		foreach ($input as $state)
			$states[$state['code']] = $state['description'];

		$free = [];
		foreach ($this->repository->findAtomicStates($entity) as $state)
		{
			$found = null;
			if (array_key_exists($state->getCode(), $states))
			{
				$state->setDescription($states[$state->getCode()]);
				$this->orm->persist($state);
				unset($states[$state->getCode()]);
			}
			else
				$free[] = $state;
		}

		foreach ($states as $code => $description)
		{
			if (!empty($free))
				$state = array_shift($free);
			else
				$state = new AtomicState($entity);

			$state->setCode($code);
			$state->setDescription($description);
			$this->orm->persist($state);
		}

		foreach ($free as $state)
			$this->orm->remove($state);
	}

	/**
	 * @param Entity         $entity
	 * @param ArgumentParser $data
	 * @param bool           $insert
	 */
	protected function setData($entity, ArgumentParser $data, bool $insert): void
	{
		Validators::validate($data, 'entity', 'invalid data for entity');

		if ($data->hasKey('name'))
			$entity->setName($data->getString('name'));
		if ($data->hasKey('code'))
		{
			$code = $data->getString('code');
			if ($checkEntity = $this->repository->getByCode($code))
				if ($checkEntity->getId() != $entity->getId())
					throw new UniqueKeyViolationException('code', $checkEntity->getId());

			$entity->setCode($code);
		}
		if ($data->hasKey('description'))
			$entity->setDescription($data->getString('description'));
		if ($data->hasKey('status'))
		{
			try {
				$entity->setStatus(EntityStatus::get($data->getString('status')));
			}
			catch (InvalidEnumValueException $e) {
				throw new InvalidArgumentException('status', $data->getString('status'), 'must be one of: ' . implode(',', EntityStatus::getAvailableValues()));
			}
		}

		if ($insert && (!$data->hasKey('code') || !$data->hasKey('name')))
			throw new MalformedInputException('Input doesn\'t contain all required fields');

		if ($entity instanceof Compartment)
		{
			Validators::validate($data, 'compartment', 'invalid data for compartment');
			if ($data->hasKey('parent'))
			{
				if ($data->hasValue('parent'))
				{
					$ent = $this->getObject($data->getInt('parent'));
					$entity->setParents([$ent]);
				}
				else
					$entity->setParents([]);
			}
		}
		elseif ($entity instanceof Complex)
		{
			Validators::validate($data, 'complex', 'invalid data for complex');
			if ($data->hasKey('compartments'))
			{
				$entities = array_map(function($id) { return $this->getObject($id); }, $data->getArray('compartments'));
				$entity->setCompartments($entities);
			}
			if ($data->hasKey('children'))
			{
				$entities = array_map(function($id) { return $this->getObject($id); }, $data->getArray('children'));
				$entity->setChildren($entities);
			}
		}
		elseif ($entity instanceof Structure)
		{
			Validators::validate($data, 'structure', 'invalid data for structure');
			if ($data->hasKey('children'))
			{
				$entities = array_map(function($id) { return $this->getObject($id); }, $data->getArray('children'));
				$entity->setChildren($entities);
			}
			if ($data->hasKey('parents'))
			{
				$entities = array_map(function($id) { return $this->getObject($id); }, $data->getArray('parents'));
				$entity->setParents($entities);
			}
		}
		elseif ($entity instanceof Atomic)
		{
			Validators::validate($data, 'atomic', 'invalid data for atomic');
			if ($data->hasKey('parents'))
			{
				$entities = array_map(function($id) { return $this->getObject($id); }, $data->getArray('parents'));
				$entity->setParents($entities);
			}
			if ($data->hasKey('states'))
				$this->setStates($entity, $data->getArray('states'));
		}
	}

	protected static function getObjectName(): string
	{
		return 'entity';
	}

	protected static function getRepositoryClassName(): string
	{
		return EntityRepositoryImpl::class;
	}
}
