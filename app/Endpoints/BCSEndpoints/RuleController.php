<?php

namespace App\Controllers;

use App\Entity\
{
	IdentifiedObject,
	Repositories\ClassificationRepository,
	Repositories\IEndpointRepository,
	Repositories\OrganismRepository,
	Repositories\RuleRepository,
	RuleAnnotation,
	Rule,
	RuleStatus
};
use App\Exceptions\MissingRequiredKeyException;
use App\Exceptions\RuleEquationException;
use App\Helpers\ArgumentParser;
use App\Helpers\Validators;
use Slim\Container;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read RuleRepository $repository
 * @method Rule getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class RuleController extends WritableRepositoryController
{
	/** @var ClassificationRepository */
	private $classificationRepository;

	/** @var OrganismRepository */
	private $organismRepository;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->classificationRepository = $c->get(ClassificationRepository::class);
		$this->organismRepository = $c->get(OrganismRepository::class);
	}

	protected static function getAllowedSort(): array
	{
		return ['id', 'name', 'code'];
	}

	protected function createEntity(ArgumentParser $data): Rule
	{
		return new Rule;
	}

	protected function getData(IdentifiedObject $rule): array
	{
		/** @var Rule $rule */
		return [
			'id' => $rule->getId(),
			'name' => $rule->getName(),
			'equation' => $rule->getEquation(),
			'description' => $rule->getDescription(),
			'code' => $rule->getCode(),
			'modifier' => $rule->getModifier(),
			'status' => (string)$rule->getStatus(),
			'classifications' => $rule->getClassifications()->map(self::identifierGetter())->toArray(),
			'organisms' => $rule->getOrganisms()->map(self::identifierGetter())->toArray(),
			'annotations' => $rule->getAnnotations()->map(function(RuleAnnotation $annotation)
			{
				return ['id' => $annotation->getTermId(), 'type' => $annotation->getTermType()];
			})->toArray(),
		];
	}

	protected function setData(IdentifiedObject $rule, ArgumentParser $data): void
	{
		/** @var Rule $rule */
		if ($data->hasKey('name'))
			$rule->setName($data->getString('name'));
		if ($data->hasKey('code'))
			$rule->setCode($data->getString('code'));
		if ($data->hasKey('equation'))
		{
			$json = json_decode(parseEquations($data->getString('equation')));
			if (!empty($json->error))
				throw new RuleEquationException($json);

			$rule->setEquation($data->getString('equation'));
		}
		if ($data->hasKey('modifier'))
			$rule->setModifier($data->getString('modifier'));
		if ($data->hasKey('description'))
			$rule->setDescription($data->getString('description'));
		if ($data->hasKey('status'))
			$rule->setStatus(RuleStatus::tryGet('status', $data->getString('status')));

		if ($data->hasKey('classifications'))
		{
			$classifications = array_map(function($id) {
				return $this->getObject((int)$id, $this->classificationRepository, 'classification');
			}, $data->getArray('classifications'));
			$rule->setClassifications($classifications);
		}

		if ($data->hasKey('organisms'))
		{
			$organisms = array_map(function($id) {
				return $this->getObject((int)$id, $this->organismRepository, 'organism');
			}, $data->getArray('organisms'));
			$rule->setOrganisms($organisms);
		}
	}

	protected static function getRepositoryClassName(): string
	{
		return RuleRepository::class;
	}

	protected static function getObjectName(): string
	{
		return 'rule';
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		return new Rule;
	}

	protected function getValidator(): Assert\Collection
	{
		return new Assert\Collection([
			'name' => new Assert\Type(['type' => 'string']),
			'equation' => new Assert\Type(['type' => 'string']),
			'modifier' => new Assert\Type(['type' => 'string']),
			'description' => new Assert\Type(['type' => 'string']),
			'status' => new Assert\Type(['type' => 'string']),
			'classifications' => Validators::$identifierList,
			'organisms' => Validators::$identifierList,
		]);
	}

	protected function checkInsertObject(IdentifiedObject $rule): void
	{
		/** @var Rule $rule */
		if ($rule->getName() == '')
			throw new MissingRequiredKeyException('name');
		if ($rule->getEquation() == '')
			throw new MissingRequiredKeyException('equation');
	}

}
