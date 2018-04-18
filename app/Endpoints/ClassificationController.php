<?php

namespace App\Controllers;

use App\Entity\Classification;
use App\Entity\EntityClassification;
use App\Entity\Repositories\ClassificationRepository;
use App\Entity\Repositories\ClassificationRepositoryImpl;
use App\Entity\RuleClassification;
use App\Exceptions\
{
	ApiException, InternalErrorException, InvalidArgumentException, NonExistingObjectException
};
use App\Helpers\ArgumentParser;
use Doctrine\ORM\ORMException;
use Slim\Container;
use Slim\Http\{Request, Response};

final class ClassificationController extends ReadableController
{
	use PageableController;
	use SortableController;

	/** @var ClassificationRepository */
	private $repository;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->repository = new ClassificationRepositoryImpl($c['em']);
	}

	protected static function getAllowedSort(): array
	{
		return ['id', 'name'];
	}

	public function read(Request $request, Response $response, ArgumentParser $args)
	{
		$filter = [];

		if ($args->hasKey('type'))
			$filter['type'] = $args->getString('type');

		$numResults = $this->repository->getNumResults($filter);
		$limit = self::getPaginationData($args, $numResults);
		$response = $response->withHeader('X-Count', $numResults);
		$response = $response->withHeader('X-Pages', $limit['pages']);

		return self::formatOk($response, $this->repository->getList($filter, self::getSort($args), $limit));
	}

	/**
	 * @param int $id
	 * @return Classification
	 * @throws ApiException
	 */
	protected function getEntity(int $id)
	{
		try {
			$ent = $this->orm->find(Classification::class, $id);
			if (!$ent)
				throw new NonExistingObjectException($id, 'classification');
		}
		catch (ORMException $e) {
			throw new InternalErrorException('Failed getting classification ID ' . $id, $e);
		}

		return $ent;
	}

	/**
	 * @param Classification $entity
	 * @return array
	 */
	protected function getData($entity): array
	{
		return [
			'id' => $entity->getId(),
			'name' => $entity->getName(),
			'type' => Classification::$classToType[get_class($entity)],
		];
	}

	/**
	 * @param Classification $entity
	 * @return array
	 */
	protected function getSingleData($entity): array
	{
		return [];
	}
}
