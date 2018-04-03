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

final class EntityController extends WritableController
{
	use SortableController;

	protected static function getAllowedSort(): array
	{
		return ['name' => 'name', 'type' => 'type', 'code' => 'code'];
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
			'classifications' => $entity->getClassifications()->map(function(EntityClassification $classification)
			{
				return $classification->getId();
			})->toArray(),
			'organisms' => $entity->getOrganisms()->map(function(Organism $organism)
			{
				return $organism->getId();
			})->toArray(),
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
				'children' => $this->orm->getRepository(Entity::class)->findComplexChildren($entity)->map(function(Organism $organism)
				{
					return $organism->getId();
				})->toArray(),
				'parent' => (($parent = $entity->getParents()->first()) ? $parent->getId() : null)
			];
		}
		elseif ($entity instanceof Complex)
		{
			return [
				'compartments' => $entity->getCompartments()->map(function(Compartment $compartment)
				{
					return $compartment->getId();
				})->toArray()
			];
		}
		elseif ($entity instanceof Structure)
		{
			return [
				'parents' => $entity->getParents()->map(function(Entity $entity)
				{
					return $entity->getId();
				})->toArray()
			];
		}
		elseif ($entity instanceof Atomic)
		{
			return [
				'parents' => $entity->getParents()->map(function(Entity $entity)
				{
					return $entity->getId();
				})->toArray(),
				'states' => $this->orm->getRepository(Entity::class)->findAtomicStates($entity)->map(function(AtomicState $state)
				{
					return ['code' => $state->getCode(), 'description' => $state->getDescription()];
				})->toArray(),
			];
		}
		else
			return [];
	}

	/**
	 * @param Entity $entity
	 * @param ArgumentParser $data
	 * @throws \Exception
	 */
	protected function setData($entity, ArgumentParser $data): void
	{
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
			if ($data->hasKey('parent'))
			{
				if ($data->hasValue('parent'))
				{
					$ent = $this->getEntity($data->getInt('parent'));
					try {
						$entity->setParents([$ent]);
					}
					catch (\TypeError $e) {
						throw new EntityLocationException($ent->getType(), $e);
					}
				}
				else
					$entity->setParents([]);
			}
		}
		elseif ($entity instanceof Complex)
		{
			if ($data->hasKey('compartment'))
			{
				$ent = $this->getEntity($data->getInt('compartment'));
				try {
					$entity->addCompartment($ent);
				}
				catch (\TypeError $e) {
					throw new EntityLocationException($ent->getType(), $e);
				}
			}
		}
		elseif ($entity instanceof Structure)
		{
			if ($data->hasKey('parent'))
			{
				$ent = $this->getEntity($data->getInt('parent'));
				try {
					$entity->addParent($ent);
				}
				catch (\TypeError $e) {
					throw new EntityHierarchyException($ent->getType(), $entity->getType(), $e);
				}
			}
		}
		elseif ($entity instanceof Atomic)
		{
			if ($data->hasKey('parent'))
			{
				$ent = $this->getEntity($data->getInt('parent'));
				try {
					$entity->addParent($ent);
				}
				catch (\TypeError $e) {
					throw new EntityHierarchyException($ent->getType(), $entity->getType(), $e);
				}
			}
		}
	}
}
