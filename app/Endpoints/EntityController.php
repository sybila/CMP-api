<?php

namespace App\Controllers;

use App\Entity\
{
	AnnotationTerm, Atomic, AtomicState, Compartment, Complex, Entity, EntityAnnotation, EntityClassification, EntityStatus, Organism, Structure
};
use App\Exceptions\
{
	ApiException, EntityHierarchyException, EntityLocationException, InternalErrorException, InvalidArgumentException, InvalidSortFieldException, NonExistingObjectException
};
use App\Helpers\ArgumentParser;
use Doctrine\ORM\ORMException;
use Slim\Http\{Request, Response};
use Symfony\Component\Validator\Validation;

final class EntityController extends WritableController
{
	use SortableController;

	protected static function getAllowedSort(): array
	{
		return ['id', 'name', 'type', 'code'];
	}

	public function read(Request $request, Response $response, ArgumentParser $args)
	{
		if ($args->hasKey('code'))
		{
			$entity = $this->orm->getRepository(Entity::class)->findOneBy(['code' => $args->getString('code')]);
			return self::formatOk(
				$response,
				$entity ? $this->getData($entity) : null
			);
		}

		$data = [];

		if ($args->hasKey('annotation'))
		{
			$parts = explode(':', $args->getString('annotation'));
			if (count($parts) !== 2)
				throw new InvalidArgumentException('annotation', $args->getString('annotation'), 'must be in format term:id');

			$term = AnnotationTerm::get(strtolower($parts[0]));
			$query = $this->orm->getRepository(Entity::class)->findByAnnotation($term, $parts[1], self::getSort($args));
		}
		elseif ($args->hasKey('name'))
			$query = $this->orm->getRepository(Entity::class)->findByName($args->getString('name'), self::getSort($args));
		else
			$query = $this->orm->getRepository(Entity::class)->findBy([], self::getSort($args));

		foreach ($query as $ent)
			$data[] = $this->getData($ent);

		return self::formatOk($response, $data);
	}

	protected function createEntity(ArgumentParser $data): Entity
	{
		if (!$data->hasKey('type'))
			throw new InvalidArgumentException('type', null);

		$cls = array_search($type = $data->getString('type'), Entity::$classToType, true);
		if (!$cls)
			throw new InvalidArgumentException('type', $type);

		return new $cls;
	}

	/**
	 * @param int $id
	 * @return Entity
	 * @throws ApiException
	 */
	protected function getEntity(int $id)
	{
		try {
			$ent = $this->orm->find(Entity::class, $id);
			if (!$ent)
				throw new NonExistingObjectException($id, 'entity');
		}
		catch (ORMException $e) {
			throw new InternalErrorException('Failed getting entity ID ' . $id, $e);
		}

		return $ent;
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
		];
	}

	/**
	 * @param Entity $entity
	 * @return array
	 */
	protected function getSingleData($entity): array
	{
		return [
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
				'children' => $this->orm->getRepository(Entity::class)
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
				'parents' => $entity->getParents()->map(self::identifierGetter())->toArray(),
				'children' => $entity->getChildren()->map(self::identifierGetter())->toArray(),
			];
		}
		elseif ($entity instanceof Atomic)
		{
			return [
				'parents' => $entity->getParents()->map(self::identifierGetter())->toArray(),
				'states' => $this->orm->getRepository(Entity::class)->findAtomicStates($entity)->map(function(AtomicState $state)
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
		foreach ($this->orm->getRepository(Entity::class)->findAtomicStates($entity) as $state)
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
	 * @param Entity $entity
	 * @param ArgumentParser $data
	 * @throws \Exception
	 */
	protected function setData($entity, ArgumentParser $data): void
	{
		Validators::validate($data, 'entity', 'invalid data for entity');
		if ($data->hasKey('name'))
			$entity->setName($data->getString('name'));
		if ($data->hasKey('code'))
			$entity->setCode($data->getString('code'));
		if ($data->hasKey('description'))
			$entity->setDescription($data->getString('description'));
		if ($data->hasKey('status'))
			$entity->setStatus(EntityStatus::fromInt($data->getInt('status')));

		if ($entity instanceof Compartment)
		{
			Validators::validate($data, 'compartment', 'invalid data for compartment');
			if ($data->hasKey('parent'))
			{
				if ($data->hasValue('parent'))
				{
					$ent = $this->getEntity($data->getInt('parent'));
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
				$entities = array_map(function($id) { return $this->getEntity($id); }, $data->getArray('compartments'));
				$entity->setCompartments($entities);
			}
			if ($data->hasKey('children'))
			{
				$entities = array_map(function($id) { return $this->getEntity($id); }, $data->getArray('children'));
				$entity->setChildren($entities);
			}
		}
		elseif ($entity instanceof Structure)
		{
			Validators::validate($data, 'structure', 'invalid data for structure');
			if ($data->hasKey('children'))
			{
				$entities = array_map(function($id) { return $this->getEntity($id); }, $data->getArray('children'));
				$entity->setChildren($entities);
			}
			if ($data->hasKey('parents'))
			{
				$entities = array_map(function($id) { return $this->getEntity($id); }, $data->getArray('parents'));
				$entity->setParents($entities);
			}
		}
		elseif ($entity instanceof Atomic)
		{
			Validators::validate($data, 'atomic', 'invalid data for atomic');
			if ($data->hasKey('parents'))
			{
				$entities = array_map(function($id) { return $this->getEntity($id); }, $data->getArray('parents'));
				$entity->setParents($entities);
			}
			if ($data->hasKey('states'))
				$this->setStates($entity, $data->getArray('states'));
		}
	}
}
