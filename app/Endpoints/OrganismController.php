<?php

namespace App\Controllers;

use App\Entity\Organism;
use App\Entity\Repositories\OrganismRepository;
use App\Entity\Repositories\OrganismRepositoryImpl;
use App\Exceptions\
{
	ApiException, InternalErrorException, NonExistingObjectException
};
use App\Helpers\ArgumentParser;
use Doctrine\ORM\ORMException;
use Slim\Container;
use Slim\Http\{Request, Response};

final class OrganismController extends ReadableController
{
	use PageableController;
	use SortableController;

	/** @var OrganismRepository */
	private $repository;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->repository = new OrganismRepositoryImpl($c['em']);
	}

	protected static function getAllowedSort(): array
	{
		return ['id', 'name', 'code'];
	}

	public function read(Request $request, Response $response, ArgumentParser $args)
	{
		$numResults = $this->repository->getNumResults([]);
		$limit = self::getPaginationData($args, $numResults);
		$response = $response->withHeader('X-Pages', $limit['pages']);
		return self::formatOk($response, $this->repository->getList([], self::getSort($args), $limit));
	}

	/**
	 * @param int $id
	 * @return Organism
	 * @throws ApiException
	 */
	protected function getEntity(int $id)
	{
		try {
			$ent = $this->repository->get($id);
			if (!$ent)
				throw new NonExistingObjectException($id, 'organism');
		}
		catch (ORMException $e) {
			throw new InternalErrorException('Failed getting organism ID ' . $id, $e);
		}

		return $ent;
	}

	/**
	 * @param Organism $entity
	 * @return array
	 */
	protected function getData($entity): array
	{
		return [
			'id' => $entity->getId(),
			'name' => $entity->getName(),
			'code' => $entity->getCode(),
		];
	}

	/**
	 * @param Organism $entity
	 * @return array
	 */
	protected function getSingleData($entity): array
	{
		return [];
	}
}
