<?php

namespace App\Controllers;

use App\Entity\IdentifiedObject;
use App\Helpers\ArgumentParser;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Repositories\IEndpointRepository;
use App\Exceptions\MissingRequiredKeyException;
use App\Exceptions\NonExistingObjectException;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

abstract class EBaseController extends WritableRepositoryController
{
	protected function getData(IdentifiedObject $object): array
	{
		return [
			'id' => $object->getId(),
		];
	}

	protected function setData(IdentifiedObject $object, ArgumentParser $body): void
	{
		!$body->hasKey('name') ? $object->setName($body->getString('name')) : $object->setName($body->getString('name'));
		/*!$body->hasKey('protocol') ?: $object->setProtocol($body->getString('protocol'));
		!$body->hasKey('description') ?: $object->setDescription($body->getString('description'));
		!$body->hasKey('started') ?: $object->setStarted($body->getString('started'));
		!$body->hasKey('status') ?: $object->setStatus($body->getString(('status')));*/
	}

	protected function getValidatorArray(): array
	{
		return [
			'id' => new Assert\Type(['type' => 'integer']),
			/*'protocol' => new Assert\Type(['type' => 'string']),
			'description' => new Assert\Type(['type' => 'string']),
			'started' => new Assert\Type(['type' => 'string']),
			'status' => new Assert\Type(['type' => 'string']),*/
		];
	}
}

abstract class ParentedEBaseController extends EBaseController
{
	/** @var IEndpointRepository */
	protected $parentRepository;
	/**
	 * Get parent repository class name
	 * @return string
	 */
	abstract protected static function getParentRepositoryClassName(): string;

	/**
	 * Get array defining format of parent object information
	 * @return array
	 */
	abstract protected function getParentObjectInfo(): array;

	/**
	 * Fetch correctly instantiated parent object
	 * @param ArgumentParser $args
	 * @return IdentifiedObject
	 * @throws MissingRequiredKeyException
	 * @throws NonExistingObjectException
	 * @throws \App\Exceptions\InvalidTypeException
	 */
	protected function getParentObject(ArgumentParser $args): IdentifiedObject
	{
		$info = static::getParentObjectInfo();
		if (!$args->hasKey($info[0]) || !is_scalar($args->get($info[0])))
			throw new MissingRequiredKeyException($info[0]);
		if (!$this->parentRepository->get($args->getInt($info[0]))) {
			throw new NonExistingObjectException($args->getString($info[0]), $info[1]);
		}
		try {
			return $this->parentRepository->get($args->getInt($info[0]));
		}
		catch (\Exception $e) {
			throw new NonExistingObjectException($args->getString($info[0]), $info[1]);
		}
	}

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->parentRepository = $c->get(static::getParentRepositoryClassName());

		$this->beforeRequest[] = function(Request $request, Response $response, ArgumentParser $args)
		{
			$this->repository->setParent($this->getParentObject($args));
		};

	}
}
