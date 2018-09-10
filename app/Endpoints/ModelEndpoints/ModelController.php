<?php

namespace App\Controllers;

use App\Entity\
{
	AnnotationTerm,
	Atomic,
	AtomicState,
	Compartment,
	Complex,
	Entity,
	EntityAnnotation,
	EntityStatus,
	Model,
	IdentifiedObject,
	Repositories\ClassificationRepository,
	Repositories\EntityRepository,
	Repositories\IEndpointRepository,
	Repositories\OrganismRepository,
	Repositories\ModelRepository,
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
 * @property-read ModelRepository $repository
 * @method Entity getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ModelController extends WritableRepositoryController
{

	/** @var ModelRepository */
	private $modelRepository;


	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->modelRepository = $c->get(ModelRepository::class);
	}

	protected static function getAllowedSort(): array
	{
		return ['id, name, userId, approvedId, status'];
	}

	protected function getData(IdentifiedObject $model): array
	{
		/** @var Model $model */
		return [
			'id' => $model->getId(),
			'name' => $model->getName(),
			'userId' => $model->getUserId(),
			'approvedId' => $model->getApprovedId(),
			'status' => (string)$model->getStatus(),
			'solver' => (string)$model->getSolver(),
			'compartments' => $model->getCompartments(),
		];
	}

	protected function setData(IdentifiedObject $model, ArgumentParser $data): void
	{
		/** @var Model $model */
		if ($data->hasKey('name'))
			$model->setName($data->getString('name'));
		if ($data->hasKey('userId'))
			$model->setUserId($data->getString('userId'));
		if ($data->hasKey('approvedId'))
			$model->setApprovedId($data->getString('approvedId'));
		if ($data->hasKey('status'))
			$model->setStatus($data->getString('status'));
		if ($data->hasKey('solver'))
			$model->setSolver($data->getString('solver'));

	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('type'))
			throw new MissingRequiredKeyException('type');

		$cls = array_search($body['type'], Entity::$classToType, true);
		return new $cls;
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
		return 'model';
	}

	protected static function getRepositoryClassName(): string
	{
		return ModelRepository::Class;
	}


}
