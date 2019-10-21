<?php

namespace App\Entity\Repositories;

use App\Entity\Device;
use App\Entity\Experiment;
use App\Entity\ExperimentDevice;
use App\Entity\IdentifiedObject;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class DeviceRepository implements IDependentSBaseRepository
{
	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\DeviceRepository */
	private $repository;

	/** @var Experiment */
	private $experiment;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(Device::class);
	}

	protected static function getParentClassName(): string
	{
		return Experiment::class;
	}

	public function get(int $id)
	{
		return $this->em->find(Device::class, $id);
	}

	public function getNumResults(array $filter): int
	{
		return ((int)$this->buildListQuery($filter)
			->select('COUNT(c)')
			->getQuery()
			->getScalarResult());
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('c.id, c.name, c.type, c.address');
		return $query->getQuery()->getArrayResult();
	}

	public function getParent(): IdentifiedObject
	{
		return $this->experiment;
	}

	public function setParent(IdentifiedObject $object): void
	{
		$className = static::getParentClassName();
		if (!($object instanceof $className))
			throw new \Exception('Parent of note must be ' . $className);
		$this->experiment = $object;
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(Device::class, 'c')
			->where('c.experiments = :experimentId')
			->setParameter('experimentId', $this->experiment->getId());
		return $query;
	}

    public function add($object): void
    {
    }

    public function remove($object): void
    {
    }
}
