<?php

namespace App\Controllers;

use App\Entity\{
	Entity,
	Model,
	IdentifiedObject,
	ModelCompartment,
	ModelReaction,
	Repositories\IEndpointRepository,
	Repositories\ModelRepository,
	Structure
};
use App\Exceptions\{
	CompartmentLocationException,
	DependentResourcesBoundException,
	InvalidArgumentException,
	MissingRequiredKeyException,
	UniqueKeyViolationException
};
use App\Helpers\ArgumentParser;
use App\Helpers\Validators;
use Slim\Container;
use Slim\Http\{
	Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read ModelRepository $repository
 * @method Entity getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ModelController extends WritableRepositoryController
{

	/** @var ModelRepository */
	private $modelRepository;


	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->modelRepository = $c->get(ModelRepository::class);
	}

	protected static function getAllowedSort(): array
	{
		return ['id, name, userId, approvedId, status'];
	}

	protected function getData(IdentifiedObject $model): array
	{
		/** @var Model $model */
		return [
			'id' => $model->getId(),
			'name' => $model->getName(),
			'userId' => $model->getUserId(),
			'approvedId' => $model->getApprovedId(),
			'description' => $model->getDescription(),
			'status' => (string)$model->getStatus(),
			'compartments' => $model->getCompartments()->map(function(ModelCompartment $compartment)
			{
				return ['id' => $compartment->getId(), 'name' => $compartment->getName()];
			})->toArray(),
			'reactions' => $model->getReactions()->map(function(ModelReaction $reaction)
			{
				return ['id' => $reaction->getId(), 'name' => $reaction->getName()];
			})->toArray(),
		];
	}

	protected function setData(IdentifiedObject $model, ArgumentParser $data): void
	{
		/** @var Model $model */
		if ($data->hasKey('name'))
			$model->setName($data->getString('name'));
		if ($data->hasKey('userId'))
			$model->setUserId($data->getString('userId'));
		if ($data->hasKey('approvedId'))
			$model->setApprovedId($data->getString('approvedId'));
		if ($data->hasKey('description'))
			$model->setDescription($data->getString('description'));
		if ($data->hasKey('status'))
			$model->setStatus($data->getString('status'));

	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('userId'))
			throw new MissingRequiredKeyException('userId');
		return new Model;
	}

	protected function checkInsertObject(IdentifiedObject $model): void
	{
		/** @var Model $model */
		if ($model->getUserId() == NULL)
			throw new MissingRequiredKeyException('userId');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		$model = $this->getObject($args->getInt('id'));
		if (!$model->getCompartments()->isEmpty())
			throw new DependentResourcesBoundException('compartment');
		if (!$model->getReactions()->isEmpty())
			throw new DependentResourcesBoundException('reaction');
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		return new Assert\Collection([
			'userId' => new Assert\Type(['type' => 'integer']),
			'name' => new Assert\Type(['type' => 'string']),
			'description' => new Assert\Type(['type' => 'string']),
			'visualisation' => new Assert\Type(['type' => 'string']),
			'status' => new Assert\Type(['type' => 'string']),
		]);
	}


	protected static function getObjectName(): string
	{
		return 'model';
	}

	protected static function getRepositoryClassName(): string
	{
		return ModelRepository::Class;
	}

	protected function getSub($entity) {
		echo $entity;
	}


}
