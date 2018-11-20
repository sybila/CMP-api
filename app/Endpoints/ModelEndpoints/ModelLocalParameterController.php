<?php

namespace App\Controllers;

use App\Entity\{
	Entity,
	ModelReaction,
	ModelReactionItem,
	ModelSpecie,
	IdentifiedObject,
	Repositories\IEndpointRepository,
	Repositories\LocalParameterRepository,
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
	NonExistingObjectException,
	UniqueKeyViolationException
};
use App\Helpers\ArgumentParser;
use App\Helpers\Validators;
use Doctrine\ORM\EntityManager;
use Slim\Container;
use Slim\Http\{
	Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read LocalParameterRepository $repository
 * @method Entity getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
abstract class ModelLocalParameterController extends ParentedRepositoryController
{

	/** @var ModelLocalParameterRepository */
	private $localParameterRepository;

	/** @var EntityManager * */
	protected $em;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->localParameterRepository = $c->get(LocalParameterRepository::class);
	}

	protected static function getAllowedSort(): array
	{
		return ['id'];
	}

	protected function getData(IdentifiedObject $localParameter): array
	{
		/** @var ModelLocalParameter $localParameter */

		return [
			'id' => $localParameter->getId(),
			'name' => $localParameter->getName(),
		];
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		return new Assert\Collection([
			'name' => new Assert\Type(['type' => 'string']),
		]);
	}

	protected static function getObjectName(): string
	{
		return 'localParameter';
	}

	protected static function getRepositoryClassName(): string
	{
		return LocalParameterRepository::class;
	}

	protected static function getParentRepositoryClassName(): string
	{
		return ModelReactionRepository::class;
	}

	protected function getParentObjectInfo(): array
	{
		return ['model-id', 'model'];
	}


	protected function setData(IdentifiedObject $localParameter, ArgumentParser $data): void
	{
		/** @var ModelLocalParameter $localParameter */
		if ($data->hasKey('name'))
			$localParameter->setName($data->getString('name'));
		if ($data->hasKey('value'))
			$localParameter->setValue($data->getInt('value'));
	}

	protected function checkInsertObject(IdentifiedObject $localParameter): void
	{
		/** @var ModelLocalParameter $localParameter */
		if ($localParameter->getReactionId() == NULL)
			throw new MissingRequiredKeyException('reactionId');
		if ($localParameter->getSpecieId() == NULL)
			throw new MissingRequiredKeyException('specieId');
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		return ModelLocalParamer;
	}
}
