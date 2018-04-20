<?php

namespace App\Controllers;

use App\Entity\IdentifiedObject;
use App\Entity\Repositories\IDependentRepository;
use App\Entity\Repositories\IRepository;
use App\Exceptions\ApiException;
use App\Exceptions\InternalErrorException;
use App\Exceptions\NonExistingObjectException;
use App\Helpers\ArgumentParser;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * @property-read IDependentRepository $repository
 */
abstract class ParentedRepositoryController extends WritableRepositoryController
{
	/** @var IRepository */
	protected $parentRepository;

	abstract protected static function getParentRepositoryClassName(): string;
	abstract protected function getParentObject(ArgumentParser $args): IdentifiedObject;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$className = static::getParentRepositoryClassName();
		$this->parentRepository = new $className($c['em']);

		$this->beforeRequest[] = function(Request $request, Response $response, ArgumentParser $args)
		{
			$this->repository->setParent($this->getParentObject($args));
		};

		$this->beforeInsert[] = function($entiity)
		{
			$this->repository->add($entiity);
		};

		$this->beforeDelete[] = function($entiity)
		{
			$this->repository->remove($entiity);
		};
	}
}
