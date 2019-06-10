<?php

namespace App\Controllers;

use App\Entity\{AnalysisTool,
    AnalysisToolSetting,
    ModelParameter,
    ModelReactionItem,
    IdentifiedObject,
    Repositories\AnalysisToolSettingRepository,
    Repositories\IEndpointRepository};
use App\Exceptions\
{
    MissingRequiredKeyException,
    NonExistingObjectException
};
use App\Helpers\ArgumentParser;
use Doctrine\ORM\EntityManager;
use Slim\Container;
use Slim\Http\{
    Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read AnalysisToolSettingRepository $repository
 * @method AnalysisToolSetting getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
abstract class AnalysisToolSettingController extends ParentedRepositoryController
{
    /** @var AnalysisToolSettingRepository */
    private $analysisToolSettingRepository;

    /** @var EntityManager * */
    protected $em;


    public function __construct(Container $c)
    {
        parent::__construct($c);
        $this->analysisToolSettingRepository = $c->get(AnalysisToolSettingRepository::class);
    }

    protected static function getAllowedSort(): array
    {
        return ['id', 'name'];
    }

    protected function getData(IdentifiedObject $analysisToolSetting): array
    {
        /** @var AnalysisToolSetting $analysisToolSetting */
        return [
            'taskId' => $analysisToolSetting->getTaskId()->getId(),
            'name' => $analysisToolSetting->getName(),
            'value' => $analysisToolSetting->getValue(),
        ];
    }

    public function delete(Request $request, Response $response, ArgumentParser $args): Response
    {
        return parent::delete($request, $response, $args);
    }

    protected function getValidator(): Assert\Collection
    {
        $validatorArray = parent::getValidatorArray();
        return new Assert\Collection(array_merge($validatorArray, [
            'name' => new Assert\Type(['type' => 'string']),
        ]));
    }

    protected function createObject(ArgumentParser $body): IdentifiedObject
    {
        if (!$body->hasKey('value'))
            throw new MissingRequiredKeyException('value');
        return new AnalysisToolSetting;
    }

    protected function checkInsertObject(IdentifiedObject $constraint): void
    {
        /** @var ModelConstraint $constraint */
        if ($constraint->getModelId() == null)
            throw new MissingRequiredKeyException('taskId');
    }


    protected static function getObjectName(): string
    {
        return 'analysisToolSetting';
    }

    protected static function getRepositoryClassName(): string
    {
        return AnalysisToolSettingRepository::class;
    }

    protected function setData(IdentifiedObject $reactionItem, ArgumentParser $data): void
    {
        /** @var ModelReactionItem reactionItem */
        parent::setData($reactionItem, $data);
        !$data->hasKey('type') ?: $reactionItem->setType($data->getString('type'));
        !$data->hasKey('value') ?: $reactionItem->setValue($data->getInt('value'));
    }

}

final class AnalysisToolsParentedSettingController extends AnalysisToolSettingController
{

    protected static function getParentRepositoryClassName(): string
    {
        return AnalysisTool::class;
    }

    protected function getParentObjectInfo(): array
    {
        return ['analysis-id', 'analysis'];
    }



    protected function checkInsertObject(IdentifiedObject $setting): void
    {
        /** @var ModelReactionItem $reactionItem */
        if ($setting->getReactionId() == null)
            throw new MissingRequiredKeyException('reactionId');
        if ($setting->getSpecieId() == null && $setting->getParameterId() === null)
            throw new MissingRequiredKeyException('specieId or parameterId');
    }

    protected function createObject(ArgumentParser $body): IdentifiedObject
    {
        if (!$body->hasKey('modelTaskId') && !$body->hasKey('analysisTool'))
            throw new MissingRequiredKeyException('specieId or parameterId');
        return new ModelReactionItem;
    }
}
