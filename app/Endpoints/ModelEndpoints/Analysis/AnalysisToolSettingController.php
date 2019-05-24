<?php

namespace App\Controllers;

use App\Entity\{AnalysisTool,
    AnalysisToolSetting,
    ModelParameter,
    ModelReaction,
    ModelReactionItem,
    ModelSpecie,
    IdentifiedObject,
    Repositories\AnalysisToolSettingRepository,
    Repositories\IEndpointRepository,
    Repositories\ModelReactionItemRepository,
    Repositories\ModelSpecieRepository,
    Repositories\ModelReactionRepository};
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
 * @method ModelReactionItem getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
abstract class AnalysisToolSettingController extends WritableRepositoryController
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

    protected static function getObjectName(): string
    {
        return 'analysisToolSetting';
    }

    protected static function getRepositoryClassName(): string
    {
        return AnalysisToolSettingRepository::class;
    }

    protected function setData(IdentifiedObject $analysisToolSetting, ArgumentParser $data): void
    {
        /** @var AnalysisToolSetting $analysisToolSetting */
        parent::setData($analysisToolSetting, $data);
        !$data->hasKey('value') ?: $analysisToolSetting->setValue($data->getInt('value'));
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


    protected function checkInsertObject(IdentifiedObject $rule): void
    {
        /** @var ModelParameter $parameter */
        if ($rule->getType() === null)
            throw new MissingRequiredKeyException('modelId');
    }

    protected function createObject(ArgumentParser $body): IdentifiedObject
    {
        return new AnalysisToolSetting;
    }
}
