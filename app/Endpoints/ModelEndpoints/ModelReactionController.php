<?php

namespace App\Controllers;

use IGroupRoleAuthWritableController;
use App\Entity\{IdentifiedObject,
    Model,
    ModelParameter,
    ModelReaction,
    ModelReactionItem,
    ModelFunction,
    Repositories\IEndpointRepository,
    Repositories\ModelReactionRepository};
use App\Exceptions\{DependentResourcesBoundException, MissingRequiredKeyException, WrongParentException};
use App\Helpers\ArgumentParser;
use SBaseControllerCommonable;
use Slim\Http\{
	Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read ModelReactionRepository $repository
 * @method ModelReaction getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ModelReactionController extends ParentedRepositoryController implements IGroupRoleAuthWritableController
{

    use SBaseControllerCommonable;


	protected static function getAllowedSort(): array
	{
		return ['id', 'name'];
	}

	protected function getData(IdentifiedObject $reaction): array
	{
		/** @var ModelReaction $reaction */
		$sBaseData = $this->getSBaseData($reaction);
		return array_merge($sBaseData, [
			'modelId' => $reaction->getModelId()->getId(),
			'compartmentId' => $reaction->getCompartmentId() ? $reaction->getCompartmentId()->getId() : null,
			'isReversible' => $reaction->getIsReversible(),
			'rate' => $reaction->getRate(),
			'reactionItems' => $reaction->getReactionItems()->map(function (ModelReactionItem $reactionItem) {
				return ['id' => $reactionItem->getId(), 'name' => $reactionItem->getName()];
			})->toArray(),
			'functions' => $reaction->getFunctions()->map(function (ModelFunction $function) {
				return ['id' => $function->getId(), 'name' => $function->getName()];
			})->toArray(),
			'parameters' => $reaction->getParameters()->map(function (ModelParameter $parameter) {
				return ['id' => $parameter->getId(), 'name' => $parameter->getName()];
			})->toArray()
		]);
	}

	protected function setData(IdentifiedObject $reaction, ArgumentParser $data): void
	{
		/** @var ModelReaction $reaction */
        $this->setSBaseData($reaction, $data);
		$reaction->getModelId() ?: $reaction->setModelId($this->repository->getParent()->getId());
		!$data->hasKey('compartmentId') ?: $reaction->setCompartmentId($data->getString('compartmentId'));
		!$data->hasKey('isReversible') ?: $reaction->setIsReversible($data->getInt('isReversible'));
		!$data->hasKey('rate') ?: $reaction->setRate($data->getString('rate'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('isReversible'))
			throw new MissingRequiredKeyException('isReversible');
		return new ModelReaction;
	}

	protected function checkInsertObject(IdentifiedObject $reaction): void
	{
		/** @var ModelReaction $reaction */
		if ($reaction->getModelId() === null)
			throw new MissingRequiredKeyException('modelId');
		if ($reaction->getIsReversible() === null)
			throw new MissingRequiredKeyException('isReversible');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		$specie = $this->getObject($args->getInt('id'));
		if (!$specie->getReactionItems()->isEmpty())
			throw new DependentResourcesBoundException('reactionItem');
		if (!$specie->getFunctions()->isEmpty())
			throw new DependentResourcesBoundException('function');
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		$validatorArray = $this->getSBaseValidator();
		return new Assert\Collection(array_merge($validatorArray, [
			'isReversible' => new Assert\Type(['type' => 'integer']),
			'rate' => new Assert\Type(['type' => 'string']),
		]));
	}

	protected static function getObjectName(): string
	{
		return 'reaction';
	}

	protected static function getRepositoryClassName(): string
	{
		return ModelReactionRepository::Class;
	}

	protected function getParentObjectInfo(): ParentObjectInfo
	{
	    return new ParentObjectInfo('model-id', Model::class);
	}

    protected function checkParentValidity(IdentifiedObject $parent, IdentifiedObject $child)
    {
        /** @var ModelReaction $child */
        if ($parent->getId() != $child->getModelId()) {
            throw new WrongParentException($this->getParentObjectInfo()->parentEntityClass, $parent->getId(),
                self::getObjectName(), $child->getId());
        }
    }
}
