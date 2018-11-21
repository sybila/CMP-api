<?php

namespace App\Controllers;

use App\Entity\{
	Entity,
	ModelReaction,
	ModelReactionItem,
	ModelParameter,
	IdentifiedObject,
	Repositories\ModelRepository,
	Repositories\IEndpointRepository,
	Repositories\ModelParameterRepository,
	Repositories\ModelReactionRepository,
	Repositories\ModelCompartmentRepository,
	Structure
};
use App\Exceptions\
{
	CompartmentLocationException,
	InvalidArgumentException,
	MissingRequiredKeyException,
	NonExistingObjectException,
	UniqueKeyViolationException
};
use App\Helpers\ArgumentParser;
use App\Helpers\Validators;
use Doctrine\ORM\EntityManager;
use Slim\Container;
use Slim\Http\{
	Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read ParameterRepository $repository
 * @method Entity getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
abstract class ModelParameterController extends ParentedRepositoryController
{

	/** @var ModelParameterRepository */
	private $parameterRepository;

	/** @var EntityManager * */
	protected $em;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->parameterRepository = $c->get(ModelParameterRepository::class);
	}

	protected static function getAllowedSort(): array
	{
		return ['id'];
	}

	protected function getData(IdentifiedObject $parameter): array
	{
		/** @var ModelReactionItem $reactionItem */

		return [
			'id' => $parameter->getId(),
			'name' => $parameter->getName(),
		];
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		return new Assert\Collection([
			'name' => new Assert\Type(['type' => 'string']),
		]);
	}

	protected static function getObjectName(): string
	{
		return 'parameter';
	}

	protected static function getRepositoryClassName(): string
	{
		return ModelParameterRepository::class;
	}

}

final class ModelParentedParameterController extends ModelParameterController
{

	protected static function getParentRepositoryClassName(): string
	{
		return ModelRepository::class;
	}

	protected function getParentObjectInfo(): array
	{
		return ['model-id', 'model'];
	}


	protected function setData(IdentifiedObject $reactionItem, ArgumentParser $data): void
	{
		/** @var ModelParameter $parameter */
	}

	protected function checkInsertObject(IdentifiedObject $reactionItem): void
	{
		/** @var ModelParameter $parameter */
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		return new ModelReactionItem;
	}
}


final class ReactionItemParentedParameterController extends ModelParameterController
{

	protected static function getParentRepositoryClassName(): string
	{
		return ModelReactionRepository::class;
	}

	protected function getParentObjectInfo(): array
	{
		return ['reactionItem-id', 'reactionItem'];
	}

	protected function setData(IdentifiedObject $reactionItem, ArgumentParser $data): void
	{
		/** @var ModelParameter $parameter */
	}

	protected function checkInsertObject(IdentifiedObject $reactionItem): void
	{
		/** @var ModelParameter $parameter */
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		return new ModelReactionItem;
	}
}
