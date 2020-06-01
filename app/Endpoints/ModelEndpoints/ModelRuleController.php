<?php

namespace App\Controllers;

use App\Entity\
{
	Entity,
	IdentifiedObject,
	ModelRule,
	Repositories\IEndpointRepository,
	Repositories\ModelRepository,
	Repositories\ModelRuleRepository
};
use App\Exceptions\
{
	InvalidArgumentException,
	MissingRequiredKeyException
};
use App\Helpers\ArgumentParser;
use Slim\Container;
use Slim\Http\{
	Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read ModelRuleRepository $repository
 * @method Entity getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
abstract class ModelRuleController extends ParentedSBaseController
{

	/** @var ModelRuleRepository */
	private $modelRuleRepository;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->modelRuleRepository = $c->get(ModelRuleRepository::class);
	}

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
		$sBaseData = parent::getData($rule);
		return array_merge($sBaseData, [
			'modelId' => $rule->getModelId()->getId(),
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

	protected static function getParentRepositoryClassName(): string
	{
		return ModelRepository::class;
	}

	protected function getParentObjectInfo(): array
	{
		return ['model-id', 'model'];
	}

	protected function setData(IdentifiedObject $rule, ArgumentParser $data): void
	{
		/** @var Rule $rule */
		parent::setData($rule, $data);
		$rule->setModelId($this->repository->getParent()->getId());
		!$data->hasKey('equation') ?: $rule->setEquation($data->getString('equation'));
		!$data->hasKey('type') ?: $rule->setType($data->getString('type'));
	}

	protected function checkInsertObject(IdentifiedObject $rule): void
	{
		/** @var ModelParameter $parameter */
		if ($rule->getType() === null)
			throw new MissingRequiredKeyException('modelId');
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		return new ModelRule;
	}
}
