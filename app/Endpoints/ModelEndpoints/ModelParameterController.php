<?php

namespace App\Controllers;

use IGroupRoleAuthWritableController;
use App\Entity\{Model,
    ModelRule,
    ModelReaction,
    ModelReactionItem,
    ModelParameter,
    IdentifiedObject,
    Repositories\IEndpointRepository,
    Repositories\ModelParameterRepository};
use App\Exceptions\{InvalidTypeException,
    MissingRequiredKeyException,
    NonExistingObjectException,
    WrongParentException};
use App\Helpers\ArgumentParser;
use Slim\Http\{
	Request, Response
};
use SBaseControllerCommonable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read ModelParameterRepository $repository
 * @method ModelParameter getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
abstract class ModelParameterController extends ParentedRepositoryController
    implements IGroupRoleAuthWritableController
{

    use SBaseControllerCommonable;

	protected static function getAllowedSort(): array
	{
		return ['id, name'];
	}


    /**
     * @param Request $request
     * @param Response $response
     * @param ArgumentParser $args
     * @return Response
     * @throws InvalidTypeException
     */
	public function readSbmlId(Request $request, Response $response, ArgumentParser $args)
	{
	    /** @var IdentifiedObject $parameter */
		$parameter = $this->repository->getBySbmlId($args->getString('sbmlId'));
		return self::formatOk(
			$response,
			$parameter ? $this->getData($parameter) : null
		);
	}

	protected function getData(IdentifiedObject $parameter): array
	{
		/** @var ModelParameter $parameter */
		$sBaseData = $this->getSBaseData($parameter);
		return array_merge($sBaseData, [
			'value' => $parameter->getValue(),
			'isConstant' => $parameter->getValue(),
			'reactionItems' => $parameter->getReactionsItems()->map(function (ModelReactionItem $reactionItem) {
				return ['id' => $reactionItem->getId(), 'name' => $reactionItem->getName()];
			})->toArray(),
			'rules' => $parameter->getRules()->map(function (ModelRule $rule) {
				return ['id' => $rule->getId(), 'equation' => $rule->getEquation()];
			})->toArray()
		]);
	}

	protected function setData(IdentifiedObject $parameter, ArgumentParser $data): void
	{
		/** @var ModelParameter $parameter */
        $this->setSBaseData($parameter, $data);
		!$data->hasKey('value') ?: $parameter->setValue($data->getString('value'));
		!$data->hasKey('isConstant') ?: $parameter->setIsConstant($data->getString('isConstant'));
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		$validatorArray = $this->getSBaseValidator();
		return new Assert\Collection(array_merge($validatorArray, [
			'value' => new Assert\Type(['type' => 'float']),
			'isConstant' => new Assert\Type(['type' => 'integer']),
		]));
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

	protected function getParentObjectInfo(): ParentObjectInfo
	{
	    return new ParentObjectInfo('model-id', Model::class);
	}

    /**
     * @param IdentifiedObject $parameter
     * @param ArgumentParser $data
     * @throws InvalidTypeException
     * @throws NonExistingObjectException
     * @throws mixed ORMException
     */
	protected function setData(IdentifiedObject $parameter, ArgumentParser $data): void
	{
		/** @var ModelParameter $parameter */
		$parameter->getModelId() ?: $parameter->setModelId($this->repository->getParent()->getId());
		if ($data->hasKey('reactionId')) {
			$reaction = $this->repository->getEntityManager()->find(ModelReaction::class, $data->getInt('reactionId'));
			if ($reaction === null) {
				throw new NonExistingObjectException($data->getInt('reactionId'), 'reaction');
			}
			$parameter->setReactionId($reaction->getId());
		}
		parent::setData($parameter, $data);
	}

	protected function checkInsertObject(IdentifiedObject $parameter): void
	{
		/** @var ModelParameter $parameter */
		if ($parameter->getSbmlId() === null)
			throw new MissingRequiredKeyException('sbmlId');
		if ($parameter->getIsConstant() === null)
			throw new MissingRequiredKeyException('isConstant');
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('isConstant'))
			throw new MissingRequiredKeyException('isConstant');
		if (!$body->hasKey('sbmlId'))
			throw new MissingRequiredKeyException('sbmlId');
		return new ModelParameter;
	}

    protected function checkParentValidity(IdentifiedObject $parent, IdentifiedObject $child)
    {
        /** @var ModelParameter $child */
        if ($parent->getId() != $child->getModelId()) {
            throw new WrongParentException($this->getParentObjectInfo()->parentEntityClass, $parent->getId(),
                self::getObjectName(), $child->getId());
        }
    }
}

final class ReactionItemParentedParameterController extends ModelParameterController
{

	protected function getParentObjectInfo(): ParentObjectInfo
	{
	    return new ParentObjectInfo('reactionItem-id', ModelReaction::class);
	}

	protected function checkInsertObject(IdentifiedObject $parameter): void
	{
		/** @var ModelParameter $parameter */
		if ($parameter->getSbmlId() === null)
			throw new MissingRequiredKeyException('sbmlId');
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('sbmlId'))
			throw new MissingRequiredKeyException('sbmlId');
		return new ModelParameter;
	}

    protected function checkParentValidity(IdentifiedObject $parent, IdentifiedObject $child)
    {
        /** @var ModelParameter $child */
        if ($parent->getId() != $child->getReactionId()) {
            throw new WrongParentException($this->getParentObjectInfo()->parentEntityClass, $parent->getId(),
                self::getObjectName(), $child->getId());
        }
    }
}
