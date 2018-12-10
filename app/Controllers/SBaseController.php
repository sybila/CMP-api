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

abstract class SBaseController extends WritableRepositoryController
{
	protected function getData(IdentifiedObject $object): array
	{
		return [
			'id' => $object->getId(),
			'name' => $object->getName(),
			'sbmlId' => $object->getSbmlId(),
			'sboTerm' => $object->getSboTerm(),
			'notes' => $object->getNotes(),
			'annotation' => $object->getAnnotation()
		];
	}

	protected function setData(IdentifiedObject $object, ArgumentParser $body): void
	{
		!$body->hasKey('name') ? $object->setName($body->getString('sbmlId')) : $object->setName($body->getString('name'));
		!$body->hasKey('sbmlId') ?: $object->setSbmlId($body->getString('sbmlId'));
		!$body->hasKey('sboTerm') ?: $object->getSboTerm($body->getString('sboTerm'));
		!$body->hasKey('notes') ?: $object->setNotes($body->getString('notes'));
		!$body->hasKey('annotation') ?: $object->setAnnotation($body->getString(('annotation')));
	}

	protected function getValidatorArray(): array
	{
		return [
			'name' => new Assert\Type(['type' => 'string']),
			'sbmlId' => new Assert\Type(['type' => 'string']),
			'sboTerm' => new Assert\Type(['type' => 'string']),
			'notes' => new Assert\Type(['type' => 'string']),
			'annotation' => new Assert\Type(['type' => 'string']),
		];
	}
}

abstract class ParentedSBaseController extends SBaseController
{
	/** @var IEndpointRepository */
	protected $parentRepository;

	abstract protected static function getParentRepositoryClassName(): string;
	abstract protected function getParentObjectInfo(): array;

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
