<?php

namespace App\Controllers;

use App\Entity\IdentifiedObject;
use App\Entity\Repositories\IEndpointRepository;
use App\Exceptions\InternalErrorException;
use App\Exceptions\NonExistingObjectException;
use App\Helpers\ArgumentParser;
use Doctrine\ORM\ORMException;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

abstract class RepositoryController extends AbstractController
{
	use SortableController;
	use PageableController;

	/** @var IEndpointRepository */
	protected $repository;

	/**
	 * function(Request $request, Response $response, ArgumentParser $args)
	 * @var callable[]
	 */
	protected $beforeRequest = [];

	abstract protected static function getRepositoryClassName(): string;
	abstract protected static function getObjectName(): string;
	abstract protected function getData(IdentifiedObject $object): array;

	/**
	 * @param array $events
	 * @param array ...$args
	 * @internal
	 */
	protected function runEvents(array $events, ...$args)
	{
		foreach ($events as $event)
			call_user_func_array($event, $args);
	}

	protected function getReadIds(ArgumentParser $args): array
	{
		return array_map(function($item)
		{
			return (int)$item;
		}, explode(',', $args->getString('id')));
	}

	protected function getFilter(ArgumentParser $args): array
	{
		return [];
	}

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->repository = $c->get(static::getRepositoryClassName());
	}

	public function read(Request $request, Response $response, ArgumentParser $args)
	{
		$this->runEvents($this->beforeRequest, $request, $response, $args);

		$filter = static::getFilter($args);
		$numResults = $this->repository->getNumResults($filter);
		$limit = static::getPaginationData($args, $numResults);
		$response = $response->withHeader('X-Count', $numResults);
		$response = $response->withHeader('X-Pages', $limit['pages']);

		return self::formatOk($response, $this->repository->getList($filter, self::getSort($args), $limit));
	}

	public function readIdentified(Request $request, Response $response, ArgumentParser $args): Response
	{
		$this->runEvents($this->beforeRequest, $request, $response, $args);

		$data = [];
		foreach ($this->getReadIds($args) as $id)
			$data[] = $this->getData($this->getObject((int)$id));

		return self::formatOk($response, $data);
	}

	/**
	 * @param int                      $id
	 * @param IEndpointRepository|null $repository
	 * @param string|null              $objectName
	 * @return mixed
	 * @throws InternalErrorException
	 * @throws NonExistingObjectException
	 */
	protected function getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
	{
		if (!$repository)
			$repository = $this->repository;
		if (!$objectName)
			$objectName = static::getObjectName();

		try {
			$ent = $repository->get($id);
			if (!$ent)
				throw new NonExistingObjectException($id, $objectName);
		}
		catch (ORMException $e) {
			throw new InternalErrorException('Failed getting ' . $objectName . ' ID ' . $id, $e);
		}

		return $ent;
	}

	// ============================================== HELPERS

	protected static function identifierGetter(): \Closure
	{
		return function(IdentifiedObject $object) { return $object->getId(); };
	}
}
