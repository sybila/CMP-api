<?php

namespace App\Controllers;

use App\Entity\IdentifiedObject;
use App\Entity\Repositories\IDependentEndpointRepository;
use App\Entity\Repositories\IEndpointRepository;
use App\Exceptions\DependentResourcesBoundException;
use App\Exceptions\MissingRequiredKeyException;
use App\Exceptions\NonExistingObjectException;
use App\Helpers\ArgumentParser;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * @property-read IDependentEndpointRepository $repository
 */
abstract class ParentedRepositoryController extends WritableRepositoryController
{
	/** @var IEndpointRepository */
	protected $parentRepository;

	abstract protected static function getParentRepositoryClassName(): string;
	abstract protected function getParentObjectInfo(): array;

	protected function getParentObject(ArgumentParser $args): IdentifiedObject
	{
		$info = static::getParentObjectInfo();
		if (!$args->hasKey($info[0]) || !is_scalar($args->get($info[0])))
			throw new MissingRequiredKeyException($info[0]);
		if (!$this->parentRepository->get($args->getInt($info[0]))) {
			throw new NonExistingObjectException($args->getString($info[0]), $info[1]);
		}
		try {
			return $this->parentRepository->get($args->getInt($info[0]));
		}
		catch (\Exception $e) {
			throw new NonExistingObjectException($args->getString($info[0]), $info[1]);
		}
	}

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->parentRepository = $c->get(static::getParentRepositoryClassName());

		$this->beforeRequest[] = function(Request $request, Response $response, ArgumentParser $args)
		{
			$this->repository->setParent($this->getParentObject($args));
		};

		$this->beforeInsert[] = function($entity)
		{
			$this->repository->add($entity);
		};

		$this->beforeDelete[] = function($entity)
		{
			$this->repository->remove($entity);
		};
	}
}
