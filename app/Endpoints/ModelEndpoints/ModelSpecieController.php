<?php

namespace App\Controllers;

use IGroupRoleAuthWritableController;
use App\Entity\{ModelCompartment,
    ModelReactionItem,
    ModelSpecie,
    ModelRule,
    IdentifiedObject,
    Repositories\ModelSpecieRepository,
    Repositories\IEndpointRepository,
    Repositories\ModelCompartmentRepository};
use App\Exceptions\{InvalidTypeException,
    MissingRequiredKeyException,
    DependentResourcesBoundException,
    WrongParentException};
use App\Helpers\ArgumentParser;
use Slim\Http\{
	Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read ModelSpecieRepository $repository
 * @method ModelSpecie getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ModelSpecieController extends ParentedRepositoryController implements IGroupRoleAuthWritableController
{
    use SBaseControllerCommonable;

	protected static function getAllowedSort(): array
	{
		return ['id', 'name'];
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
	    /** @var ModelSpecie $specie */
		$specie = $this->repository->getBySbmlId($args->getString('sbmlId'));
		return self::formatOk(
			$response,
			$specie ? $this->getData($specie) : null
		);
	}

	protected function getData(IdentifiedObject $specie): array
	{
		/** @var ModelSpecie $specie */
		$sBaseData = $this->getSBaseData($specie);
		return array_merge($sBaseData, [
			'initialExpression' => $specie->getInitialExpression(),
			'hasOnlySubstanceUnits' => $specie->getHasOnlySubstanceUnits(),
			'constant' => $specie->getConstant(),
			'boundaryCondition' => $specie->getBoundaryCondition(),
			'reactionItems' => $specie->getReactionItems()->map(function (ModelReactionItem $reactionItem) {
				return ['id' => $reactionItem->getId(), 'name' => $reactionItem->getName()];
			})->toArray(),
			'rules' => $specie->getRules()->map(function (ModelRule $rule) {
				return ['id' => $rule->getId(), 'equation' => $rule->getExpression()];
			})->toArray()
		]);
	}

	protected function setData(IdentifiedObject $specie, ArgumentParser $data): void
	{
		/** @var ModelSpecie $specie */
		$this->setSBaseData($specie, $data);
		$specie->setModelId($this->repository->getParent()->getModel()->getId());
		if(!$specie->getCompartmentId()) {
		    /** @var ModelCompartment $compartment */
		    $compartment = $this->repository->getParent();
            $specie->setCompartmentId($compartment);
        }
		!$data->hasKey('initialExpression') ?: $specie->setInitialExpression($data->getString('initialExpression'));
		!$data->hasKey('boundaryCondition') ?: $specie->setBoundaryCondition($data->getBool('boundaryCondition'));
		!$data->hasKey('hasOnlySubstanceUnits') ?: $specie->setHasOnlySubstanceUnits($data->getBool('hasOnlySubstanceUnits'));
		!$data->hasKey('constant') ?: $specie->setConstant($data->getBool('constant'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
	    $specie = new ModelSpecie;
		if (!$body->hasKey('isConstant')) {
            $specie->setConstant(false);
        }
		if (!$body->hasKey('hasOnlySubstanceUnits'))
            $specie->setHasOnlySubstanceUnits(true);
		return $specie;
	}

	protected function checkInsertObject(IdentifiedObject $specie): void
	{
		/** @var ModelSpecie $specie */
		if ($specie->getModelId() === null)
			throw new MissingRequiredKeyException('modelId');
		if ($specie->getCompartmentId() === null)
			throw new MissingRequiredKeyException('compartmentId');
		if ($specie->getHasOnlySubstanceUnits() === null)
			throw new MissingRequiredKeyException('hasOnlySubstanceUnits');
		if ($specie->getConstant() === null)
			throw new MissingRequiredKeyException('constant');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		$specie = $this->getObject($args->getInt('id'));
		if (!$specie->getRules()->isEmpty())
			throw new DependentResourcesBoundException('rule');
		if (!$specie->getReactionItems()->isEmpty())
			throw new DependentResourcesBoundException('reactionItem');
        $this->deleteAnnotations($args->getInt('id'));
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		$validatorArray = $this->getSBaseValidator();
		return new Assert\Collection(array_merge($validatorArray, [
			'equationType' => new Assert\Type(['type' => 'string']),
			'initialExpression' => new Assert\Type(['type' => 'string']),
			'boundaryCondition' => new Assert\Type(['type' => 'integer']),
			'hasOnlySubstanceUnits' => new Assert\Type(['type' => 'integer']),
			'constant' => new Assert\Type(['type' => 'integer']),
		]));
	}

	protected static function getObjectName(): string
	{
		return 'specie';
	}

	protected static function getRepositoryClassName(): string
	{
		return ModelSpecieRepository::Class;
	}

	protected static function getParentRepositoryClassName(): string
	{
		return ModelCompartmentRepository::class;
	}

	protected function getParentObjectInfo(): ParentObjectInfo
	{
	    return new ParentObjectInfo('compartment-id', ModelCompartment::class);
	}

    protected function checkParentValidity(IdentifiedObject $parent, IdentifiedObject $child)
    {
        /** @var ModelSpecie $child */
        if ($parent->getId() != $child->getCompartmentId()->getId()) {
            throw new WrongParentException($this->getParentObjectInfo()->parentEntityClass, $parent->getId(),
                self::getObjectName(), $child->getId());
        }
    }
}
