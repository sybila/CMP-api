<?php

namespace App\Controllers;

use IGroupRoleAuthWritableController;
use App\Entity\{MathExpression,
    Model,
    ModelFunctionDefinition,
    IdentifiedObject,
    ModelInitialAssignment,
    Repositories\IEndpointRepository,
    Repositories\ModelFunctionDefinitionRepository};
use App\Exceptions\{MissingRequiredKeyException, WrongParentException};
use App\Helpers\ArgumentParser;
use Slim\Http\{
	Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Radoslav Doktor & Marek Havlík
 * @property-read ModelFunctionDefinitionRepository $repository
 * @method ModelFunctionDefinition getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ModelFunctionDefinitionController extends ParentedRepositoryController
    implements IGroupRoleAuthWritableController
{

    use SBaseControllerCommonable;


	protected static function getAllowedSort(): array
	{
		return ['id', 'name'];
	}

	protected function getData(IdentifiedObject $functionDefinition): array
	{
		/** @var ModelFunctionDefinition $functionDefinition */
		$sBaseData = $this->getSBaseData($functionDefinition);
		return array_merge($sBaseData, [
			'expression' => [
                'latex' => is_null($functionDefinition->getExpression()) ? ''
                    : $functionDefinition->getExpression()->getLatex(),
                'cmml' => is_null($functionDefinition->getExpression()) ? ''
                    : $functionDefinition->getExpression()->getContentMML()]
		]);
	}

	protected function setData(IdentifiedObject $functionDefinition, ArgumentParser $data): void
	{
		/** @var ModelFunctionDefinition $functionDefinition */
        $this->setSBaseData($functionDefinition, $data);
		$functionDefinition->getModelId() ?: $functionDefinition->setModelId($this->repository->getParent());
		if ($data->hasKey('expression')) {
            $expr = $functionDefinition->getExpression();
            $expr->setContentMML($data->getString('expression'), true);
            $functionDefinition->setExpression($expr);
        }
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
        $expr = new MathExpression();
        $modFn = new ModelFunctionDefinition();
        $modFn->setExpression($expr);
        return $modFn;
	}

	protected function checkInsertObject(IdentifiedObject $functionDefinition): void
	{
		/** @var ModelFunctionDefinition $functionDefinition */
		if ($functionDefinition->getModelId() == null)
			throw new MissingRequiredKeyException('modelId');
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
			'formula' => new Assert\Type(['type' => 'string'])
		]));
	}

	protected static function getObjectName(): string
	{
		return 'modelFunctionDefinition';
	}

	protected static function getRepositoryClassName(): string
	{
		return ModelFunctionDefinitionRepository::Class;
	}

	protected function getParentObjectInfo(): ParentObjectInfo
	{
	    return new ParentObjectInfo('model-id', Model::class);
	}

    protected function checkParentValidity(IdentifiedObject $model, IdentifiedObject $child)
    {
        /** @var ModelFunctionDefinition $child */
        if ($model->getId() != $child->getModelId()->getId()) {
            throw new WrongParentException($this->getParentObjectInfo()->parentEntityClass, $model->getId(),
                self::getObjectName(), $child->getId());
        }
    }
}
