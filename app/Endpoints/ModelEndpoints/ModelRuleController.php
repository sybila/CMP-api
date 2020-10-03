<?php

namespace App\Controllers;

use App\Entity\{Entity,
    IdentifiedObject,
    Model,
    ModelParameter,
    ModelRule,
    Repositories\IEndpointRepository,
    Repositories\ModelRepository,
    Repositories\ModelRuleRepository};
use IAuthWritableRepositoryController;
use App\Exceptions\{InvalidArgumentException, MissingRequiredKeyException, WrongParentException};
use App\Helpers\ArgumentParser;
use SBaseControllerCommonable;
use Slim\Container;
use Slim\Http\{
	Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read ModelRuleRepository $repository
 * @method Entity getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
abstract class ModelRuleController extends ParentedRepositoryController implements IAuthWritableRepositoryController
{

    use SBaseControllerCommonable;

    protected static function getAlias(): string
    {
        return 'r';
    }

	protected static function getAllowedSort(): array
	{
		return ['id'];
	}

	protected function getData(IdentifiedObject $rule): array
	{
		/** @var ModelRule $rule */
		$sBaseData = $this->getSBaseData($rule);
		return array_merge($sBaseData, [
			'modelId' => $rule->getModelId(),
		]);
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('type'))
			throw new MissingRequiredKeyException('type');

		$cls = array_search($body['type'], Entity::$classToType, true);
		return new $cls;
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		try {
			$a = parent::delete($request, $response, $args);
		} catch (\Exception $e) {
			throw new InvalidArgumentException('annotation', $args->getString('annotation'), 'must be in format term:id');
		}
		return $a;
	}

	protected function getValidator(): Assert\Collection
	{
		return new Assert\Collection([
			'name' => new Assert\Type(['type' => 'string']),
		]);
	}

	protected static function getObjectName(): string
	{
		return 'modelRule';
	}

	protected static function getRepositoryClassName(): string
	{
		return ModelRuleRepository::Class;
	}

}

final class ModelParentedRuleController extends ModelRuleController
{

	protected function getParentObjectInfo(): ParentObjectInfo
	{
	    return new ParentObjectInfo('model-id', Model::class);
	}

	protected function setData(IdentifiedObject $rule, ArgumentParser $data): void
	{
		/** @var ModelRule $rule */
        $this->setSBaseData($rule, $data);
		$rule->setModelId($this->repository->getParent()->getId());
		!$data->hasKey('equation') ?: $rule->setEquation($data->getString('equation'));
		!$data->hasKey('type') ?: $rule->setType($data->getString('type'));
	}

	protected function checkInsertObject(IdentifiedObject $rule): void
	{
		/** @var ModelRule $rule */
		if ($rule->getType() === null)
			throw new MissingRequiredKeyException('modelId');
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		return new ModelRule;
	}

    protected function checkParentValidity(IdentifiedObject $parent, IdentifiedObject $child)
    {
        // TODO: Implement checkParentValidity() method.
    }
}
