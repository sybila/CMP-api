<?php

namespace App\Controllers;

use App\Entity\IdentifiedObject;
use App\Entity\Repositories\IDependentEndpointRepository;
use App\Exceptions\MissingRequiredKeyException;
use App\Exceptions\NonExistingObjectException;
use App\Exceptions\WrongParentException;
use App\Helpers\ArgumentParser;
use Exception;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * @property-read IDependentEndpointRepository $repository
 */
abstract class ParentedRepositoryController extends WritableRepositoryController
{

    /**
     * Returns object with one variable - string that marks how is the parent-id
     * labelled in the route (how the ArgumentParser parses this argument)
     * and the second element is the name of the class entity.
     * @return ParentObjectInfo
     */
	abstract protected function getParentObjectInfo(): ParentObjectInfo;

    /**
     * Throws an error if paternity test fails.
     * @param IdentifiedObject $parent entity
     * @param IdentifiedObject $child
     * @throws WrongParentException
     */
	abstract protected function checkParentValidity(IdentifiedObject $parent, IdentifiedObject $child);

    /**
     * @param ArgumentParser $args
     * @return IdentifiedObject
     * @throws MissingRequiredKeyException
     * @throws NonExistingObjectException
     */
	protected function getParentObject(ArgumentParser $args): IdentifiedObject
    {
        $info = static::getParentObjectInfo();
        try {
            $id = $args->get($info->parentIdRoutePlaceholder);
        } catch (Exception $e) {
            throw new MissingRequiredKeyException($e->getMessage());
        }
        return $this->getObjectViaORM($info->parentEntityClass, $id);
	}



	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->beforeRequest[] = function(Request $request, Response $response, ArgumentParser $args)
		{
			$this->repository->setParent($this->getParentObject($args));
		};
		$this->beforeInsert[] = function($entity)
		{
            $this->checkParentValidity($this->repository->getParent(), $entity);
			$this->repository->add($entity);
		};
        $this->beforeUpdate[] = function($entity)
        {
            $this->checkParentValidity($this->repository->getParent(), $entity);
        };
		$this->beforeDelete[] = function($entity)
		{
		    $this->checkParentValidity($this->repository->getParent(), $entity);
			$this->repository->remove($entity);
		};
	}

    /**
     * @inheritDoc
     * @throws WrongParentException
     */
    public function readIdentified(Request $request, Response $response, ArgumentParser $args): Response
    {
        $this->runEvents($this->beforeRequest, $request, $response, $args);
        $id = current($this->getReadIds($args));
        $ent = $this->getObject((int)$id);
        $this->validateDetail();
        $data = $this->getData($ent);
        $this->checkParentValidity($this->repository->getParent(), $ent);
        return self::formatOk($response, $data);
    }
}

/**
 * Class ParentObjectInfo defines the info that is needed for parent validation
 * @package App\Controllers
 */
class ParentObjectInfo
{
    /**
     * Short entity class name.
     * @var string
     */
    public $parentEntityClass;

    /**
     * This has to reflect the SLIM route placeholder.
     * @var string
     */
    public $parentIdRoutePlaceholder;

    /**
     * ParentObjectInfo constructor.
     * @param string $parentEntityClass
     * @param string $parentIdRoutePlaceholder
     */
    public function __construct(string $parentIdRoutePlaceholder, string $parentEntityClass)
    {
        $this->parentEntityClass = $parentEntityClass;
        $this->parentIdRoutePlaceholder = $parentIdRoutePlaceholder;
    }
}

