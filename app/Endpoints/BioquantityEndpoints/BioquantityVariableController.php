<?php

namespace App\Controllers;

use App\Entity\{BioquantityVariable,
    Bioquantity,
    BioquantityMethod,
    ExperimentVariable,
    IdentifiedObject,
    Repositories\BioquantityMethodRepository,
    Repositories\BioquantityVariableRepository,
    Repositories\ExperimentVariableRepository,
    Repositories\IEndpointRepository};

use App\Exceptions\
{
	MissingRequiredKeyException,
	DependentResourcesBoundException
};
use App\Helpers\ArgumentParser;
use Slim\Container;
use Slim\Http\{
	Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read BioquantityVariableRepository $repository
 * @method BioquantityVariable getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class BioquantityVariableController extends ParentedRepositoryController
{

	/** @var BioquantityVariableRepository */
	private $variableRepository;
    private $experimentVariableRepository;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->variableRepository = $c->get(BioquantityVariableRepository::class);
        $this->experimentVariableRepository = $c->get(ExperimentVariableRepository::class);
	}

	protected static function getAllowedSort(): array
	{
		return ['id', 'name', 'timeFrom', 'timeTo', 'value'];
	}


	protected function getData(IdentifiedObject $variable): array
	{
		/** @var BioquantityVariable $variable */
        if($variable != null) {
		return [
            'name' => $variable->getName(),
			'experimentVariableId' => $variable->getExperimentVariableId() != null ? ExperimentVariableController::getData($variable->getExperimentVariableId()):null,
			'timeFrom' => $variable->getTimeFrom(),
            'timeTo' => $variable->getTimeTo(),
			'value' => $variable->getValue(),
		];
        }
	}

	protected function setData(IdentifiedObject $variable, ArgumentParser $data): void
	{
		/** @var BioquantityVariable $variable */
        $variable->getMethodId() ?: $variable->setMethodId($this->repository->getParent());
        !$data->hasKey('experimentVariableId') ?: $variable->setExperimentVariableId($this->experimentVariableRepository->get($data->getInt('experimentVariableId')));
        !$data->hasKey('timeFrom') ?: $variable->setTimeFrom($data->getFloat('timeFrom'));
		!$data->hasKey('timeTo') ?: $variable->setTimeTo($data->getFloat('timeTo'));
		!$data->hasKey('value') ?: $variable->setValue($data->getFloat('value'));
        !$data->hasKey('name') ?: $variable->setName($data->getString('name'));
        !$data->hasKey('source') ?: $variable->setSource($data->getString('source'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
       if (!$body->hasKey('name'))
            throw new MissingRequiredKeyException('name');
		return new BioquantityVariable();
	}

	protected function checkInsertObject(IdentifiedObject $variable): void
	{
		/** @var BioquantityVariable $variable */
		if ($variable->getMethodId() === null)
			throw new MissingRequiredKeyException('methodId');
        if ($variable->getName() === null)
            throw new MissingRequiredKeyException('name');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		$variable = $this->getObject($args->getInt('id'));
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
		return 'variable';
	}

	protected static function getRepositoryClassName(): string
	{
		return BioquantityVariableRepository::Class;
	}

	protected static function getParentRepositoryClassName(): string
	{
		return BioquantityMethodRepository::class;
	}

	protected function getParentObjectInfo(): array
	{
		return ['method-id', 'method'];
	}

    protected static function getAlias(): string
    {
        return 'v';
    }
}
