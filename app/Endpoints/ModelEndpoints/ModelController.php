<?php

namespace App\Controllers;

use App\Entity\{
    Authorization\User,
    Model,
    IdentifiedObject,
    ModelCompartment,
    ModelConstraint,
    ModelEvent,
    ModelFunctionDefinition,
    ModelInitialAssignment,
    ModelParameter,
    ModelReaction,
    ModelRule,
    Repositories\IEndpointRepository,
    Repositories\ModelRepository};
use App\Exceptions\{DependentResourcesBoundException,
    InvalidRoleException,
    MissingRequiredKeyException};
use App\Helpers\ArgumentParser;
use IGroupRoleAuthWritableController;
use Slim\Http\{
	Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read ModelRepository $repository
 * @method Model getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ModelController extends WritableRepositoryController implements IGroupRoleAuthWritableController
{
    use SBaseControllerCommonable;

    protected static function getRepositoryClassName(): string
    {
        return ModelRepository::class;
    }

    protected static function getObjectName(): string
    {
        return 'model';
    }

	protected static function getAllowedSort(): array
	{
		return ['id', 'name', 'userId', 'approvedId', 'status'];
	}

	protected function getData(IdentifiedObject $model): array
	{
	    /** @var Model $model */
		$sBaseData = $this->getSBaseData($model);
		return array_merge($sBaseData, [
			'userId' => $model->getUserId(),
			'groupId' => $model->getGroupId(),
			'description' => $model->getDescription(),
			'status' => (string) $model->getStatus(),
			'isPublic' => (bool) $model->isPublic(),
			'compartments' => $model->getCompartments()->map(function (ModelCompartment $compartment) {
				return ['id' => $compartment->getId(), 'alias'=> $compartment->getSbmlId(), 'name' => $compartment->getName()];
			})->toArray(),
			'constraints' => $model->getConstraints()->map(function (ModelConstraint $constraint) {
				return ['id' => $constraint->getId(), 'formula' => $constraint->getFormula()];
			})->toArray(),
			'events' => $model->getEvents()->map(function (ModelEvent $event) {
				return ['id' => $event->getId(), 'name' => $event->getName()];
			})->toArray(),
			'functionDefinitions' => $model->getFunctionDefinitions()->map(function (ModelFunctionDefinition $functionDefinition) {
				return ['id' => $functionDefinition->getId(), 'name' => $functionDefinition->getName()];
			})->toArray(),
			'initialAssignments' => $model->getInitialAssignments()->map(function (ModelInitialAssignment $initialAssignment) {
				return ['id' => $initialAssignment->getId(), 'formula' => $initialAssignment->getFormula()];
			})->toArray(),
			'parameters' => $model->getParameters()->map(function (ModelParameter $parameter) {
				return ['id' => $parameter->getId(), 'name' => $parameter->getName()];
			})->toArray(),
			'reactions' => $model->getReactions()->map(function (ModelReaction $reaction) {
				return ['id' => $reaction->getId(), 'name' => $reaction->getName()];
			})->toArray(),
			'rules' => $model->getRules()->map(function (ModelRule $rule) {
			    return ['id' => $rule->getId(), 'equation' => $rule->getEquation()];
			})->toArray(),
//			'unitDefinitions' => $model->getUnitDefinitions()->map(function (ModelUnitDefinition $unitDefinition) {
//				return ['id' => $unitDefinition->getId(), 'name' => $unitDefinition->getName()];
//			})->toArray()
		]);
	}

    /**
     * @inheritDoc
     * @throws InvalidRoleException
     */
	protected function setData(IdentifiedObject $model, ArgumentParser $data): void
	{
		/** @var Model $model */
		$this->setSBaseData($model, $data);
		$model->getUserId() != null ?: $model->setUserId($this->userPermissions['user_id']);
        !$data->hasKey('groupId') && ($model->getGroupId() === null) ?
            $model->setGroupId(array_key_first($this->userPermissions['group_wise'])) :
            $model->setGroupId($data->getString('groupId'));
        !$data->hasKey('description') ?: $model->setDescription($data->getString('description'));
		//status should be set automatically when proper actions are taken
        //!$data->hasKey('status') ?: $model->setStatus($data->getString('status'));
		if ($data->hasKey('isPublic'))
        {
            $data->getBool('isPublic') == false ? $model->setIsPublic(false) :
                $model->setIsPublic($this->canPublish($model->getStatus()));
        }
	}

	protected function canPublish(string $status): bool
    {
        $role = $this->userPermissions['platform_wise'];
        if ( ($role == User::ADMIN || $role == User::POWER) && ($status != Model::INCOMPLETE) ) {
            return true;
        }
        throw new InvalidRoleException("publish the model",'PUT/POST',$_SERVER['REQUEST_URI']);
    }

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('name'))
			throw new MissingRequiredKeyException('name');
		$model = new Model;
		$model->setStatus(Model::INCOMPLETE);
		$model->setIsPublic(false);
		return $model;
	}

    /**
     * @inheritDoc
     * @throws InvalidRoleException
     */
	protected function checkInsertObject(IdentifiedObject $model): void
	{
		/** @var Model $model */
        if ($this->userPermissions['platform_wise'] != User::ADMIN &&
            !in_array($this->userPermissions['group_wise'][$model->getGroupId()],User::CAN_ADD))
            throw new InvalidRoleException("assign group ID = {$model->getGroupId()} to this ",
                'POST',$_SERVER['REQUEST_URI']);
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		$model = $this->getObject($args->getInt('id'));
		if (!$model->getCompartments()->isEmpty())
			throw new DependentResourcesBoundException('compartment');
		if (!$model->getConstraints()->isEmpty())
			throw new DependentResourcesBoundException('constraints');
		if (!$model->getEvents()->isEmpty())
			throw new DependentResourcesBoundException('events');
		if (!$model->getFunctionDefinitions()->isEmpty())
			throw new DependentResourcesBoundException('functionDefinitions');
		if (!$model->getInitialAssignments()->isEmpty())
			throw new DependentResourcesBoundException('initialAssignments');
		if (!$model->getParameters()->isEmpty())
			throw new DependentResourcesBoundException('parameters');
		if (!$model->getRules()->isEmpty())
			throw new DependentResourcesBoundException('rules');
		if (!$model->getReactions()->isEmpty())
			throw new DependentResourcesBoundException('reactions');
		if (!$model->getUnitDefinitions()->isEmpty())
			throw new DependentResourcesBoundException('unitDefinitions');
        $this->deleteAnnotations($args->getInt('id'));
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		$validatorArray = $this->getSBaseValidator();
		return new Assert\Collection(array_merge($validatorArray, [
		    'groupId' => new Assert\Type(['type' => 'integer']),
			'userId' => new Assert\Type(['type' => 'integer']),
			'description' => new Assert\Type(['type' => 'string']),
			'visualisation' => new Assert\Type(['type' => 'string']),
			'status' => new Assert\Type(['type' => 'string']),
		]));
	}

}
