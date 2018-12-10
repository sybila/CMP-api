<?php

namespace App\Controllers;

use App\Entity\{
	IdentifiedObject,
	ModelFunction,
	Repositories\IEndpointRepository,
	Repositories\ModelFunctionRepository,
	Repositories\ModelReactionRepository
};
use App\Helpers\ArgumentParser;
use Slim\Container;
use Slim\Http\{
	Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read ModelReactionRepository $repository
 * @method ModelFunction getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ModelFunctionController extends ParentedRepositoryController
{
	/** @var FunctionRepository */
	private $functionRepository;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->functionRepository = $c->get(ModelFunctionRepository::class);
	}

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
		$function->getReactionId() ?: $function->setReactionId($this->repository->getParent());
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

	protected static function getParentRepositoryClassName(): string
	{
		return ModelReactionRepository::class;
	}

	protected function getParentObjectInfo(): array
	{
		return ['reaction-id', 'reaction'];
	}

}

