<?php

namespace App\Controllers;

use IGroupRoleAuthWritableController;
use MathMLContentToPresentation as MathML;
use App\Entity\{Compartment,
    Model,
    ModelCompartment,
    ModelFunctionDefinition,
    ModelSpecie,
    ModelReaction,
    ModelRule,
    ModelUnitDefinition,
    IdentifiedObject,
    Repositories\IEndpointRepository,
    Repositories\ModelRepository,
    Repositories\ModelCompartmentRepository};
use App\Exceptions\{DependentResourcesBoundException, MissingRequiredKeyException, WrongParentException};
use App\Helpers\ArgumentParser;
use Slim\Http\{
	Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read ModelCompartmentRepository $repository
 * @method ModelCompartment getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ModelCompartmentController extends ParentedRepositoryController implements IGroupRoleAuthWritableController
{
    use SBaseControllerCommonable;


	protected static function getAllowedSort(): array
	{
		return ['id', 'name'];
	}

	protected function getData(IdentifiedObject $compartment): array
	{
//	    /** @var Model $model */
//	    $model = $this->repository->getParent();
//	    $defs = [];
//	    $model->getFunctionDefinitions()->map(function (ModelFunctionDefinition $fnDef) use (&$defs) {
//	        $defs[$fnDef->getAlias()] = $fnDef->getExpression();
//        });
//	    dump($defs);exit;
        /** @var ModelCompartment $compartment */
		$sBaseData = $this->getSBaseData($compartment);
		return array_merge ($sBaseData, [
			'spatialDimensions' => $compartment->getSpatialDimensions(),
			'size' => $compartment->getSize(),
			'constant' => $compartment->getConstant(),
			'species' => $compartment->getSpecies()->map(function (ModelSpecie $specie) {
				return ['id' => $specie->getId(), 'name' => $specie->getName()];
			})->toArray(),
			'reactions' => $compartment->getReactions()->map(function (ModelReaction $reaction) {
				return ['id' => $reaction->getId(), 'name' => $reaction->getName()];
			})->toArray(),
            'rules' => $compartment->getRules()
                ->filter(function (ModelRule $rule) use ($compartment) {
                    return $rule->getCompartmentId() == $compartment;
                })->map(function (ModelRule $rule) {
                    return ['id' => $rule->getId(),
                        'type' => $rule->getType(),
                        'equation' => [
                            'latex' => is_null($rule->getExpression()) ? '' :$rule->getExpression()->getLatex(),
                            'cmml' => is_null($rule->getExpression()) ? '' : $rule->getExpression()->getContentMML()]];
            })->toArray()
//			'rules' => $compartment->getRules()->map(function (ModelRule $rule) {
//				return ['id' => $rule->getId(), 'equation' => [
//                    'latex' => is_null($rule->getExpression()) ? '' :$rule->getExpression()->getLatex(),
//                    'cmml' => is_null($rule->getExpression()) ? '' : $rule->getExpression()->getContentMML()]];
//			})->toArray(),
//			'unitDefinitions' => $compartment->getUnitDefinitions()->map(function (ModelUnitDefinition $unit) {
//				return ['id' => $unit->getId(), 'symbol' => $unit->getSymbol()];
//			})->toArray(),
		]);
	}

	protected function setData(IdentifiedObject $compartment, ArgumentParser $data): void
	{
		/** @var ModelCompartment $compartment */
		$this->setSBaseData($compartment, $data);
		!$data->hasKey('spatialDimensions') ?: $compartment->setSpatialDimensions($data->getString('spatialDimensions'));
		!$data->hasKey('size') ?: $compartment->setSize($data->getString('size'));
		!$data->hasKey('constant') ?: $compartment->setConstant($data->getBool('constant'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('alias'))
			throw new MissingRequiredKeyException('alias');
        $compartment = new ModelCompartment;
        if (!$body->hasKey('constant')) {
		    $compartment->setConstant(true);
        }
		$compartment->setModel($this->repository->getParent());
		return $compartment;
	}

	protected function checkInsertObject(IdentifiedObject $compartment): void
	{
		/** @var ModelCompartment $compartment */
		if ($compartment->getAlias() === null)
			throw new MissingRequiredKeyException('sbmlId');
		if ($compartment->getConstant() === null)
			throw new MissingRequiredKeyException('constant');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
        $compartment = $this->getObject($args->getInt('id'));
		if (!$compartment->getSpecies()->isEmpty())
			throw new DependentResourcesBoundException('specie');
		if (!$compartment->getRules()->isEmpty())
			throw new DependentResourcesBoundException('rules');
		if (!$compartment->getReactions()->isEmpty())
			throw new DependentResourcesBoundException('reaction');
//		if (!$compartment->getUnitDefinitions()->isEmpty())
//			throw new DependentResourcesBoundException('unitDefinitions');
        $this->deleteAnnotations($args->getInt('id'));
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		$validatorArray = $this->getSBaseValidator();
		return new Assert\Collection(array_merge($validatorArray, [
			'modelId' => new Assert\Type(['type' => 'integer']),
			'constant' => new Assert\Type(['type' => 'integer']),
			'spatialDimensions' => new Assert\Type(['type' => 'double']),
			'size' => new Assert\Type(['type' => 'double']),
		]));
	}

	protected static function getObjectName(): string
	{
		return 'modelCompartment';
	}

	protected static function getRepositoryClassName(): string
	{
		return ModelCompartmentRepository::Class;
	}

	protected static function getParentRepositoryClassName(): string
	{
		return ModelRepository::class;
	}

	protected function getParentObjectInfo(): ParentObjectInfo
	{
	    return new ParentObjectInfo('model-id',Model::class);
	}

    protected function checkParentValidity(IdentifiedObject $parent, IdentifiedObject $child)
    {
        if ($parent != $child->getModel()) {
            throw new WrongParentException($this->getParentObjectInfo()->parentEntityClass, $parent->getId(),
                self::getObjectName(), $child->getId());
        }
    }
}
