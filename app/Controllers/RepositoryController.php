<?php

namespace App\Controllers;

use App\Entity\IdentifiedObject;
use App\Entity\Repositories\IEndpointRepository;
use App\Exceptions\EmptySelectionException;
use App\Exceptions\InternalErrorException;
use App\Exceptions\NonExistingObjectException;
use App\Helpers\ArgumentParser;
use App\Repositories\Authorization\UserRepository;
use Doctrine\ORM\ORMException;
use DoctrineProxies\__CG__\App\Entity\Authorization\User;
use League\OAuth2\Server\ResourceServer;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

abstract class RepositoryController extends AbstractController
{

	use SortableController, PageableController, RepoAccessController, FilterableController;

	/** @var IEndpointRepository */
	protected $repository;

	/**
	 * function(Request $request, Response $response, ArgumentParser $args)
	 * @var callable[]
	 */
	protected $beforeRequest = [];

	/** @var ResourceServer */
	protected $server;

	/** @var UserRepository */
    protected $user;

    /** @var User */
    protected $logged_user;

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
		return array_map(function($item) {
			return (int) $item;
		}, explode(',', $args->getString('id')));
	}


	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->repository = $c->get(static::getRepositoryClassName());
		$this->server = $c->get(ResourceServer::class);
		$this->user = $c->get(UserRepository::class);
	}


	public function read(Request $request, Response $response, ArgumentParser $args)
	{
	    $this->runEvents($this->beforeRequest, $request, $response, $args);

	    $filter['accessFilter'] = static::validateList($request, $this->server, $this->user);
        $filter['argFilter'] = static::getFilter($args);
        $numResults = $this->repository->getNumResults($filter);
        static::validateFilter($numResults, $filter['argFilter']);

        $limit = static::getPaginationData($args, $numResults);
		$response = $response->withHeader('X-Count', $numResults);
		$response = $response->withHeader('X-Pages', $limit['pages']);

		return self::formatOk($response, $this->repository->getList($filter, self::getSort($args), $limit));
	}


	public function readIdentified(Request $request, Response $response, ArgumentParser $args): Response
	{
		$this->runEvents($this->beforeRequest, $request, $response, $args);
        $data = [];
		foreach ($this->getReadIds($args) as $id) {
            $ent = $this->getObject((int)$id);
            static::validateDetail($request, $this->server, $this->user);
		    $data = static::getPaginationOnDetail($args, $this->getData($ent));
            if (array_key_exists('maxCount', $data)){
                $maxCount = $data['maxCount'];
                $response = $response->withHeader('X-MaxCount', $maxCount);
                $response = $response->withHeader('X-Pages', $args['perPage'] ? ceil($maxCount / $args['perPage']) : 1);
                unset($data['maxCount']);
            }
        }
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
		} catch (ORMException $e) {
			throw new InternalErrorException('Failed getting ' . $objectName . ' ID ' . $id, $e);
		}

		return $ent;
	}


	// ============================================== HELPERS

	protected static function identifierGetter(): \Closure
	{
		return function(IdentifiedObject $object) {
			return $object->getId();
		};
	}

}
