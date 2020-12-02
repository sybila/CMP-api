<?php

namespace App\Controllers;

use App\Exceptions\MissingRequiredKeyException;
use App\Exceptions\WrongParentException;
use IGroupRoleAuthWritableController;
use App\Entity\{IdentifiedObject,
    ModelFunction,
    ModelReaction,
    Repositories\IEndpointRepository,
    Repositories\ModelFunctionRepository,
    Repositories\ModelReactionRepository};
use App\Helpers\ArgumentParser;
use Slim\Http\{
	Request, Response
};
use SBaseControllerCommonable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read ModelReactionRepository $repository
 * @method ModelFunction getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ModelFunctionController extends ParentedRepositoryController implements IGroupRoleAuthWritableController
{

    use SBaseControllerCommonable;

	protected static function getAllowedSort(): array
	{
		return ['id', 'name'];
	}

	protected function getData(IdentifiedObject $function): array
	{
		/** @var ModelFunction $function */
		return [
			'id' => $function->getId(),
			'name' => $function->getName(),
			'formula' => $function->getFormula()
		];
	}

	protected function setData(IdentifiedObject $function, ArgumentParser $data): void
	{
		/** @var ModelFunction $function */
		$function->getReactionId() ?: $function->setReactionId($this->repository->getParent()->getId());
		!$data->hasKey('name') ? $function->setName($data->getString('sbmlId')) : $function->setName($data->getString('name'));
		!$data->hasKey('formula') ?: $function->setFormula($data->getString('formula'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		return new ModelFunction;
	}

	protected function checkInsertObject(IdentifiedObject $object): void
	{
		/** @var ModelFunction $function */
		if ($function->getReactionId() == null)
			throw new MissingRequiredKeyException('reactionId');
		if ($function->getName() == null)
			throw new MissingRequiredKeyException('name');
		if ($function->getName() == null)
			throw new MissingRequiredKeyException('sbmlId');
		if ($function->getFormula() == null)
			throw new MissingRequiredKeyException('formula');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		return new Assert\Collection([
			'reactionId' => new Assert\Type(['type' => 'integer']),
			'name' => new Assert\Type(['type' => 'string']),
			'formula' => new Assert\Type(['type' => 'string'])
		]);
	}

	protected static function getObjectName(): string
	{
		return 'function';
	}

	protected static function getRepositoryClassName(): string
	{
		return ModelFunctionRepository::class;
	}

	protected function getParentObjectInfo(): ParentObjectInfo
	{
	    return new ParentObjectInfo('reaction-id', ModelReaction::class);
	}

    protected function checkParentValidity(IdentifiedObject $reaction, IdentifiedObject $child)
    {
        /** @var ModelFunction $child */
        if ($reaction->getId() != $child->getReactionId()->getId()) {
            throw new WrongParentException($this->getParentObjectInfo()->parentEntityClass, $reaction->getId(),
                self::getObjectName(), $child->getId());
        }
    }
}

