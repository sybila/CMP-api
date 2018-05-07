<?php

namespace App\Controllers;

use App\Entity\
{
	AnnotationTerm,
	Atomic,
	AtomicState,
	Compartment,
	Complex,
	Entity,
	EntityAnnotation,
	EntityClassification,
	EntityStatus,
	IdentifiedObject,
	Repositories\ClassificationRepository,
	Repositories\EntityRepository,
	Repositories\IEndpointRepository,
	Repositories\OrganismRepository,
	Structure
};
use App\Exceptions\
{
	InvalidArgumentException, InvalidEnumFieldValueException, MalformedInputException, UniqueKeyViolationException
};
use App\Helpers\ArgumentParser;
use App\Helpers\Validators;
use Consistence\Enum\InvalidEnumValueException;
use Slim\Container;
use Slim\Http\{Request, Response};
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read EntityRepository $repository
 * @method Entity getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class EntityController extends WritableRepositoryController
{
	/** @var ClassificationRepository */
	private $classificationRepository;

	/** @var OrganismRepository */
	private $organismRepository;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->classificationRepository = $c->get(ClassificationRepository::class);
		$this->organismRepository = $c->get(OrganismRepository::class);
	}

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

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('type'))
			throw new InvalidArgumentException('type', null);

		$cls = array_search($type = $body->getString('type'), Entity::$classToType, true);
		if (!$cls || $cls == AtomicState::class)
			throw new InvalidArgumentException('type', $type);

		return new $cls;
	}

	protected function getData(IdentifiedObject $object): array
	{
		/** @var Entity $object */
		return [
			'id' => $object->getId(),
			'name' => $object->getName(),
			'description' => $object->getDescription(),
			'code' => $object->getCode(),
			'type' => $object->getType(),
			'status' => (string)$object->getStatus(),
			'classifications' => $object->getClassifications()->map(self::identifierGetter())->toArray(),
			'organisms' => $object->getOrganisms()->map(self::identifierGetter())->toArray(),
			'annotations' => $object->getAnnotations()->map(function(EntityAnnotation $annotation)
			{
				return ['id' => $annotation->getTermId(), 'type' => $annotation->getTermType()];
			})->toArray(),
		] + $this->getSpecificData($object);
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

	protected function setData(IdentifiedObject $entity, ArgumentParser $data): void
	{
		/** @var Entity $entity */
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

		if ($data->hasKey('classifications'))
		{
			$classifications = array_map(function($id) {
				return $this->getObject((int)$id, $this->classificationRepository, 'classification');
			}, $data->getArray('classifications'));
			$entity->setClassifications($classifications);
		}

		if ($data->hasKey('organisms'))
		{
			$organisms = array_map(function($id) {
				return $this->getObject((int)$id, $this->organismRepository, 'organism');
			}, $data->getArray('organisms'));
			$entity->setOrganisms($organisms);
		}

		$this->validate($data, $this->getSpecificValidator($entity->getType()));

		if ($entity instanceof Compartment)
		{
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
			if ($data->hasKey('parents'))
			{
				$entities = array_map(function($id) { return $this->getObject($id); }, $data->getArray('parents'));
				$entity->setParents($entities);
			}
		}
		elseif ($entity instanceof Atomic)
		{
			if ($data->hasKey('compartments'))
			{
				$entities = array_map(function($id) { return $this->getObject($id); }, $data->getArray('compartments'));
				$entity->setCompartments($entities);
			}
			if ($data->hasKey('parents'))
			{
				$entities = array_map(function($id) { return $this->getObject($id); }, $data->getArray('parents'));
				$entity->setParents($entities);
			}
			if ($data->hasKey('states'))
				$this->setStates($entity, $data->getArray('states'));
		}
	}

	protected function getValidator(): Assert\Collection
	{
		return new Assert\Collection([
			'name' => new Assert\Type(['type' => 'string']),
			'code' => Validators::$code,
			'type' => new Assert\Choice(array_values(Entity::$classToType)),
			'description' => new Assert\Type(['type' => 'string']),
			'status' => new Assert\Type(['type' => 'string']),
			'classifications' => Validators::$identifierList,
			'organisms' => Validators::$identifierList,
		]);
	}

	protected function getSpecificValidator(string $type): Assert\Collection
	{
		switch ($type)
		{
			case 'compartment': return new Assert\Collection([
				'parent' => Validators::$identifier
			]);
			case 'complex': return new Assert\Collection([
				'compartments' => Validators::$identifierList,
				'children' => Validators::$identifierList,
			]);
			case 'structure': return new Assert\Collection([
				'compartments' => Validators::$identifierList,
				'parents' => Validators::$identifierList,
				'children' => Validators::$identifierList,
			]);
			case 'atomic': return new Assert\Collection([
				'compartments' => Validators::$identifierList,
				'parents' => Validators::$identifierList,
				'states' => new Assert\All([
					new Assert\Collection([
						'code' => new Assert\NotBlank(),
						'description' => new Assert\Type(['type' => 'string']),
					])
				]),
			]);
		}

		return null;
	}

	protected static function getObjectName(): string
	{
		return 'entity';
	}

	protected static function getRepositoryClassName(): string
	{
		return EntityRepository::class;
	}

	protected function checkInsertObject(IdentifiedObject $entity): void
	{
		/** @var Entity $entity */
		if ($entity->getCode() == '' || $entity->getName() == '')
			throw new MalformedInputException('Input doesn\'t contain all required fields');
	}
}
