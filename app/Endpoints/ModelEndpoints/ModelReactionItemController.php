<?php

namespace App\Controllers;

use Exception;
use IGroupRoleAuthWritableController;
use App\Entity\{
	ModelParameter,
	ModelReaction,
	ModelReactionItem,
	ModelSpecie,
	IdentifiedObject,
	Repositories\IEndpointRepository,
	Repositories\ModelReactionItemRepository
};
use App\Exceptions\{
    MissingRequiredKeyException,
    NonExistingObjectException,
    WrongParentException};
use App\Helpers\ArgumentParser;
use Slim\Http\{
	Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read ModelReactionItemRepository $repository
 * @method ModelReactionItem getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
abstract class ModelReactionItemController extends ParentedRepositoryController implements IGroupRoleAuthWritableController
{

    use SBaseControllerCommonable;


	protected static function getAllowedSort(): array
	{
		return ['id', 'name'];
	}

	protected function getData(IdentifiedObject $reactionItem): array
	{
		/** @var ModelReactionItem $reactionItem */
		$sBaseData = $this->getSBaseData($reactionItem);
		return array_merge($sBaseData, [
			'reactionId' => $reactionItem->getReactionId()->getId(),
			'specieId' => $reactionItem->getSpecieId() ? $reactionItem->getSpecieId()->getId() : null,
			'parameterId' => $reactionItem->getParameterId() ? $reactionItem->getParameterId()->getId() : null,
			'type' => $reactionItem->getType(),
			'stoichiometry' => $reactionItem->getStoichiometry()
		]);
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
			'name' => new Assert\Type(['type' => 'string']),
		]));
	}

	protected static function getObjectName(): string
	{
		return 'reactionItem';
	}

	protected static function getRepositoryClassName(): string
	{
		return ModelReactionItemRepository::class;
	}

	protected function setData(IdentifiedObject $reactionItem, ArgumentParser $data): void
	{
		/** @var ModelReactionItem $reactionItem */
        $this->setSBaseData($reactionItem, $data);
		!$data->hasKey('type') ?: $reactionItem->setType($data->getString('type'));
		!$data->hasKey('value') ?: $reactionItem->setValue($data->getInt('value'));
		!$data->hasKey('stoichiometry') ?: $reactionItem->setStochiometry($data->getFloat('stoichiometry'));
	}

}

final class ReactionParentedReactionItemController extends ModelReactionItemController
{

	protected function getParentObjectInfo(): ParentObjectInfo
	{
	    return new ParentObjectInfo('reaction-id', ModelReaction::class);
	}

    /**
     * @param IdentifiedObject $reactionItem
     * @param ArgumentParser $data
     * @throws NonExistingObjectException
     * @throws mixed
     */
	protected function setData(IdentifiedObject $reactionItem, ArgumentParser $data): void
	{
		/** @var ModelReactionItem $reactionItem */
		$reactionItem->getReactionId() ?: $reactionItem->setReactionId($this->repository->getParent()->getId());
		if ($data->hasKey('specieId')) {
			if ($data->hasKey('parameterId')) {
				throw new Exception('reaction item cannot refer to specie and parameter at the same time');
			}

			/** @var ModelSpecie $specie */
			$specie = $this->getObjectViaORM(ModelSpecie::class, $data->getInt('specieId'));
			if ($specie === null) {
				throw new NonExistingObjectException($data->getInt('specieId'), 'specie');
			}
			$reactionItem->setParameterId(null);
			$reactionItem->setSpecieId($specie);
		} else {

		    /** @var ModelParameter $parameter */
		    $parameter = $this->getObjectViaORM(ModelParameter::class, $data->getInt('parameterId'));
			if ($parameter === null) {
				throw new NonExistingObjectException($data->getInt('parameterId'), 'parameter');
			}
			$reactionItem->setSpecieId(null);
			$reactionItem->setParameterId($parameter);
		}
		parent::setData($reactionItem, $data);
	}

	protected function checkInsertObject(IdentifiedObject $reactionItem): void
	{
		/** @var ModelReactionItem $reactionItem */
		if ($reactionItem->getReactionId() == null)
			throw new MissingRequiredKeyException('reactionId');
		if ($reactionItem->getSpecieId() == null && $reactionItem->getParameterId() === null)
			throw new MissingRequiredKeyException('specieId or parameterId');
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('specieId') && !$body->hasKey('parameterId'))
			throw new MissingRequiredKeyException('specieId or parameterId');
		return new ModelReactionItem;
	}

    protected function checkParentValidity(IdentifiedObject $parent, IdentifiedObject $child)
    {
        /** @var ModelReactionItem $child */
        if ($parent->getId() != $child->getReactionId()->getId()) {
            throw new WrongParentException($this->getParentObjectInfo()->parentEntityClass, $parent->getId(),
                self::getObjectName(), $child->getId());
        }
    }
}

final class SpecieParentedReactionItemController extends ModelReactionItemController
{

	protected function getParentObjectInfo(): ParentObjectInfo
	{
	    return new ParentObjectInfo('specie-id', ModelSpecie::class);
	}

    /**
     * @inheritDoc
     * @throws mixed
     */
	protected function setData(IdentifiedObject $reactionItem, ArgumentParser $data): void
	{
		/** @var ModelReactionItem $reactionItem */
        if (!$reactionItem->getSpecieId()) {
            /** @var ModelSpecie $specie */
            $specie = $this->repository->getParent();
            $reactionItem->setSpecieId($specie);
        }
		if ($data->hasKey('reactionId')) {
		    $reaction = $this->getObjectViaORM(ModelReaction::class, $data->getInt('reactionId'));
			if ($reaction === null) {
				throw new NonExistingObjectException($data->getInt('reactionId'), 'reaction');
			}
			$reactionItem->setReactionId($reaction->getId());
		}
		parent::setData($reactionItem, $data);
	}

	protected function checkInsertObject(IdentifiedObject $reactionItem): void
	{
		/** @var ModelReactionItem $reactionItem */
		if ($reactionItem->getReactionId() == null)
			throw new MissingRequiredKeyException('reactionId');
		if ($reactionItem->getSpecieId() == null)
			throw new MissingRequiredKeyException('specieId');
	}

    /**
     * @param ArgumentParser $body
     * @return IdentifiedObject
     * @throws mixed
     */
	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('reactionId'))
			throw new MissingRequiredKeyException('reactionId');
		if ($body->hasKey('parameterId'))
			throw new Exception('reaction item cannot refer to specie and parameter at the same time');
		return new ModelReactionItem;
	}

    protected function checkParentValidity(IdentifiedObject $parent, IdentifiedObject $child)
    {
        /** @var ModelReactionItem $child */
        if ($parent->getId() != $child->getSpecieId()->getId()) {
            throw new WrongParentException($this->getParentObjectInfo()->parentEntityClass, $parent->getId(),
                self::getObjectName(), $child->getId());
        }
    }
}
