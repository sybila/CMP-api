<?php

namespace App\Controllers;

use App\Entity\{
	Entity,
	ModelUnitToDefinition,
	IdentifiedObject,
	ModelFunction,
	ModelReaction,
	Repositories\IEndpointRepository,
	Repositories\FunctionRepository,
	Repositories\ModelSpecieRepository,
	Repositories\ModelReactionRepository,
	Repositories\ModelCompartmentRepository,
	Structure
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
 * @property-read ModelReactionRepository $repository
 * @method Entity getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ModelFunctionController extends ParentedRepositoryController
{

	/** @var FunctionRepository */
	private $functionRepository;

	public function __construct(Container $c)

	{
		parent::__construct($c);
		$this->functionRepository = $c->get(FunctionRepository::class);
	}

	protected static function getAllowedSort(): array
	{

		return ['id'];
	}

	protected function getData(IdentifiedObject $function): array
	{
		/** @var ModelReactionItem $function */

		return [
			'id' => $function->getId(),
			'name' => $function->getName(),
			'formula' => $function->getFormula()
		];
	}

	protected function setData(IdentifiedObject $function, ArgumentParser $data): void
	{
		/** @var ModelFunction $function */
		if(!$function->getReactionId())
			$function->setReactionId($this->repository->getParent());
		if ($data->hasKey('name'))
			$function->setName($data->getString('name'));
		if ($data->hasKey('formula'))
			$function->setFormula($data->getString('formula'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		return new ModelFunction;
	}

	protected function checkInsertObject(IdentifiedObject $object): void
	{
		//todo
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
		return 'function';
	}

	protected static function getRepositoryClassName(): string
	{
		return FunctionRepository::class;
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

