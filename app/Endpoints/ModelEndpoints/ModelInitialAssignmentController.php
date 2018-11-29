<?php

namespace App\Controllers;

use App\Entity\{
	ModelInitialAssignment,
	IdentifiedObject,
	Repositories\IEndpointRepository,
	Repositories\ModelRepository,
	Repositories\ModelInitialAssignmentRepository
};
use App\Exceptions\
{
	MissingRequiredKeyException
};
use App\Helpers\ArgumentParser;
use Slim\Container;
use Slim\Http\{
	Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read ModelInitialAssignmentRepository $repository
 * @method ModelInitialAssignment getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ModelInitialAssignmentController extends ParentedRepositoryController
{
	/** @var ModelInitialAssignmentRepository */
	private $initialAssignmentRepository;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->initialAssignmentRepository = $c->get(ModelInitialAssignmentRepository::class);
	}

	protected static function getAllowedSort(): array
	{
		return ['id', 'name'];
	}

	protected function getData(IdentifiedObject $initialAssignment): array
	{
		/** @var ModelInitialAssignment $initialAssignment */
		return [
			'id' => $initialAssignment->getId(),
			'sbmlId' => $initialAssignment->getSbmlId(),
			'formula' => $initialAssignment->getFormula(),
		];
	}

	protected function setData(IdentifiedObject $initialAssignment, ArgumentParser $data): void
	{
		/** @var ModelInitialAssignment $initialAssignment */
		$initialAssignment->getModelId() ?: $initialAssignment->setModelId($this->repository->getParent());
		!$data->hasKey('formula') ?: $initialAssignment->setFormula($data->getString('formula'));
		!$data->hasKey('sbmlId') ?: $initialAssignment->setSbmlId($data->getString('sbmlId'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		return new ModelInitialAssignment();
	}

	protected function checkInsertObject(IdentifiedObject $initialAssignment): void
	{
		/** @var ModelInitialAssignment $initialAssignment */
		if ($initialAssignment->getModelId() == null)
			throw new MissingRequiredKeyException('modelId');
		if ($initialAssignment->getFormula() == null)
			throw new MissingRequiredKeyException('formula');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		return new Assert\Collection([
			'modelId' => new Assert\Type(['type' => 'integer']),
			'formula' => new Assert\Type(['type' => 'string'])
		]);
	}

	protected static function getObjectName(): string
	{
		return 'modelInitialAssignment';
	}

	protected static function getRepositoryClassName(): string
	{
		return ModelInitialAssignmentRepository::Class;
	}

	protected static function getParentRepositoryClassName(): string
	{
		return ModelRepository::class;
	}

	protected function getParentObjectInfo(): array
	{
		return ['model-id', 'model'];
	}
}
