<?php

namespace App\Controllers;

use App\Entity\
{
	Entity,
	IdentifiedObject,
	Repositories\IEndpointRepository,
	Repositories\ModelRepository,
	Repositories\ModelRuleRepository
};
use App\Exceptions\
{
	CompartmentLocationException,
	InvalidArgumentException,
	MissingRequiredKeyException,
	UniqueKeyViolationException
};
use App\Helpers\ArgumentParser;
use App\Helpers\Validators;
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

	protected static function getAllowedSort(): array
	{
		return ['id'];
	}

	protected function getData(IdentifiedObject $rule): array
	{
		/** @var ModelRule $rule */
		return [
			'id' => $rule->getId(),
			'modelId' => $rule->getModelId(),
		];
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

final class ModelParentedRuleController extends ModelRuleController {


	protected static function getParentRepositoryClassName(): string
	{
		return ModelRepository::class;
	}

	protected function getParentObjectInfo(): array
	{
		return ['model-id', 'model'];
	}


	protected function setData(IdentifiedObject $reactionItem, ArgumentParser $data): void
	{
		/** @var ModelParameter $parameter */
	}

	protected function checkInsertObject(IdentifiedObject $reactionItem): void
	{
		/** @var ModelParameter $parameter */
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		return new ModelReactionItem;
	}

}
