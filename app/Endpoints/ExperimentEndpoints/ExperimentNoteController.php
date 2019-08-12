<?php

namespace App\Controllers;

use App\Entity\{
	ExperimentNote,
	IdentifiedObject,
	Repositories\IEndpointRepository,
	Repositories\ExperimentRepository,
	Repositories\ExperimentNoteRepository
};
use App\Exceptions\
{
	DependentResourcesBoundException,
	MissingRequiredKeyException
};
use App\Helpers\ArgumentParser;
use Slim\Container;
use Slim\Http\{
	Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read ExperimentNoteRepository $repository
 * @method ExperimentNote getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ExperimentNoteController extends ParentedEBaseController
{
	/** @var ExperimentNoteRepository */
	private $noteRepository;

	public function __construct(Container $v)
	{
		parent::__construct($v);
		$this->noteRepository = $v->get(ExperimentNoteRepository::class);
	}

	protected static function getAllowedSort(): array
	{
		return ['id', 'time', 'note'];
	}

	protected function getData(IdentifiedObject $note): array
	{
		/** @var ExperimentNote $note */
		$eBaseData = parent::getData($note);
		return array_merge ($eBaseData, [
			'time' => $note->getTime(),
			'note' => $note->getNote(),
			'imgLink' => $note->getImgLink(),
		]);
	}

	protected function setData(IdentifiedObject $note, ArgumentParser $data): void
	{
		/** @var ExperimentNote $note */
		parent::setData($note, $data);
		$note->getExperimentId() ?: $note->setExperimentId($this->repository->getParent());
		!$data->hasKey('time') ?: $note->setTime($data->getFloat('time'));
		!$data->hasKey('note') ?: $note->setNote($data->getString('note'));
		!$data->hasKey('imgLink') ?: $note->setImgLink($data->getString('imgLink'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		/*if (!$body->hasKey('time'))
			throw new MissingRequiredKeyException('time');
		/*if (!$body->hasKey('note'))
			throw new MissingRequiredKeyException('note');
		/*if (!$body->hasKey('imgLink'))
			throw new MissingRequiredKeyException('imgLink');*/
		return new ExperimentNote;
	}

	protected function checkInsertObject(IdentifiedObject $note): void
	{
		/** @var ExperimentNote $note */
		if ($note->getExperimentId() === null)
			throw new MissingRequiredKeyException('experimentId');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		/** @var ExperimentNote $note */
		$note = $this->getObject($args->getInt('id'));
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		$validatorArray = parent::getValidatorArray();
		return new Assert\Collection(array_merge($validatorArray, [
			'experimentId' => new Assert\Type(['type' => 'integer']),
		]));
	}

	protected static function getObjectName(): string
	{
		return 'experimentNote';
	}

	protected static function getRepositoryClassName(): string
	{
		return ExperimentNoteRepository::Class;
	}

	protected static function getParentRepositoryClassName(): string
	{
		return ExperimentRepository::class;
	}

	protected function getParentObjectInfo(): array
	{
		return ['experiment-id', 'experiment'];
	}
}
