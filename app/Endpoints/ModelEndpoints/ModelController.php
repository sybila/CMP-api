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
use Slim\Http\{Request, Response};
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

	protected function setData(IdentifiedObject $object, ArgumentParser $body): void
	{ //todo
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		return null;
	}

	protected function checkInsertObject(IdentifiedObject $object): void
	{
		//todo
	}


	public function add(Request $request, Response $response, ArgumentParser $args): Response
	{
		return null;
	}

	public function edit(Request $request, Response $response, ArgumentParser $args): Response
	{
		return null;
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		return null;
	}

	protected function getValidator(): Assert\Collection
	{
		return null;
	}


	protected static function getObjectName(): string
	{
		return 'model';
	}

	protected static function getRepositoryClassName(): string
	{
		return ModelRepository::Class;
	}

	protected function getData(IdentifiedObject $object): array
	{
		return null;
	}

	protected static function getAllowedSort(): array
	{
		return null;
	}
}
