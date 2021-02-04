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
abstract class ParentedRepositoryController extends MultiParentedRepoController
{

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

