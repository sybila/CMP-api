<?php

namespace App\Controllers;

use App\Entity\{AnalysisTool,
    AnalysisToolSetting,
    IdentifiedObject,
    Repositories\AnalysisToolRepository,
    Repositories\IEndpointRepository};
use App\Exceptions\{
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
 * @property-read AnalysisToolRepository $repository
 * @method AnalysisTool getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class AnalysisToolController extends WritableRepositoryController
{
    /** @var AnalysisToolRepository */
    private $analysisToolRepository;

    public function __construct(Container $c)
    {
        parent::__construct($c);
        $this->analysisToolRepository = $c->get(AnalysisToolRepository::class);
    }

    protected static function getAllowedSort(): array
    {
        return ['id, name'];
    }

    protected function getData(IdentifiedObject $analysisTool): array
    {
        /** @var AnalysisTool $analysisTool */
        return [
            'id' => $analysisTool->getId(),
            'name' => $analysisTool->getName(),
            'description' => $analysisTool->getDescription(),
            'cmd' => $analysisTool->getCmd(),
            'vizId' => $analysisTool->getVizId(),
            'location' => $analysisTool->getLocation(),
            'settings' => $analysisTool->getAnalysisToolSettings()->map(function (AnalysisToolSetting $analSet) {
                return ['id' => $analSet->getId(), 'name' => $analSet->getName(), 'default_value' =>$analSet->getValue()];
            })->toArray(),
        ];
    }

    protected function setData(IdentifiedObject $analysisTool, ArgumentParser $data): void
    {
        /** @var AnalysisTool $analysisTool */
        parent::setData($analysisTool, $data);
        !$data->hasKey('vizId') ?: $analysisTool->setVizId($data->getString('userId'));
        !$data->hasKey('description') ?: $analysisTool->setDescription($data->getString('description'));
        !$data->hasKey('cmd') ?: $analysisTool->setCmd($data->getString('cmd'));
    }

    protected function createObject(ArgumentParser $body): IdentifiedObject
    {
        if (!$body->hasKey('vizId'))
            throw new MissingRequiredKeyException('vizId');
        return new AnalysisTool();
    }

    protected function checkInsertObject(IdentifiedObject $analysisTool): void
    {
        /** @var AnalysisTool $analysisTool */
        if ($analysisTool->getVizId() === null)
            throw new MissingRequiredKeyException('vizId');
    }

    public function delete(Request $request, Response $response, ArgumentParser $args): Response
    {
        /** @var AnalysisTool $analysisTool */
        $analysisTool = $this->getObject($args->getInt('id'));
        if (!$analysisTool->getAnalysisToolSettings()->isEmpty())
            throw new DependentResourcesBoundException('analysisToolSetting');
        return parent::delete($request, $response, $args);
    }

    protected function getValidator(): Assert\Collection
    {
        return new Assert\Collection([
            'vizId' => new Assert\Type(['type' => 'integer']),
            'description' => new Assert\Type(['type' => 'string']),
            'cmd' => new Assert\Type(['type' => 'string']),
            'name' => new Assert\Type(['type' => 'string']),
        ]);
    }

    protected static function getObjectName(): string
    {
        return 'analysisTool';
    }

    protected static function getRepositoryClassName(): string
    {
        return AnalysisToolRepository::Class;
    }

    protected function getSub($entity)
    {
        echo $entity;
    }

}
