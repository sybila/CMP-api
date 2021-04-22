<?php

namespace App\Controllers;

use IGroupRoleAuthWritableController;
use App\Entity\{Model,
    ModelDataset,
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
		$parameter = $this->repository->getBySbmlId($args->getString('alias'));
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
			'constant' => $parameter->getValue(),
			'reactionItems' => $parameter->getReactionsItems()->map(function (ModelReactionItem $reactionItem) {
				return ['id' => $reactionItem->getId(), 'name' => $reactionItem->getName()];
			})->toArray(),
			'rule' => ['id' => $parameter->getRule()->getId(), 'expression' => is_null($parameter->getRule()->getExpression()) ?
                    ['latex' => '', 'cmml' => ''] :
                    ['latex' => $parameter->getRule()->getExpression()->getLatex(),
                    'cmml' => $parameter->getRule()->getExpression()->getContentMML()]]
        ]);
	}

	protected function setData(IdentifiedObject $parameter, ArgumentParser $data): void
	{
		/** @var ModelParameter $parameter */
        $this->setSBaseData($parameter, $data);
		!$data->hasKey('value') ?: $parameter->setValue($data->getString('value'));
		!$data->hasKey('constant') ?: $parameter->setIsConstant($data->getString('constant'));
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
	    $this->deleteAnnotations($args->getInt('id'));
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		$validatorArray = $this->getSBaseValidator();
		return new Assert\Collection(array_merge($validatorArray, [
			'value' => new Assert\Type(['type' => 'float']),
			'constant' => new Assert\Type(['type' => 'integer']),
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
		$parameter->getModel() ?: $parameter->setModel();
		if ($data->hasKey('reactionId')) {
			$reaction = $this->repository->getEntityManager()->find(ModelReaction::class, $data->getInt('reactionId'));
			if ($reaction === null) {
				throw new NonExistingObjectException($data->getInt('reactionId'), 'reaction');
			}
			$parameter->setReaction($reaction->getId());
		}
		parent::setData($parameter, $data);
	}

	protected function checkInsertObject(IdentifiedObject $parameter): void
	{
		/** @var ModelParameter $parameter */
		if ($parameter->getAlias() === null)
			throw new MissingRequiredKeyException('alias');
		if ($parameter->getIsConstant() === null)
			throw new MissingRequiredKeyException('constant');
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('alias'))
			throw new MissingRequiredKeyException('alias');
		if (!$body->hasKey('value')){
            throw new MissingRequiredKeyException('value');
        }
		/** @var Model $model */
		$model = $this->repository->getParent();
        $modelParameter = new ModelParameter($model,$body->get('value'));
        if (!$body->hasKey('constant')) {
            $modelParameter->setIsConstant(false);
        }
        return $modelParameter;
	}

    protected function checkParentValidity(IdentifiedObject $parent, IdentifiedObject $child)
    {
        /** @var ModelParameter $child */
        if ($parent->getId() != $child->getModel()->getId()) {
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
		if ($parameter->getAlias() === null)
			throw new MissingRequiredKeyException('alias');
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('alias'))
			throw new MissingRequiredKeyException('alias');
		return new ModelParameter;
	}

    protected function checkParentValidity(IdentifiedObject $parent, IdentifiedObject $child)
    {
        /** @var ModelParameter $child */
        if ($parent->getId() != $child->getReaction()->getId()) {
            throw new WrongParentException($this->getParentObjectInfo()->parentEntityClass, $parent->getId(),
                self::getObjectName(), $child->getId());
        }
    }
}
