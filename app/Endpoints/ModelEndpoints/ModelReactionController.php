<?php

namespace App\Controllers;

use IGroupRoleAuthWritableController;
use App\Entity\{IdentifiedObject,
    MathExpression,
    Model,
    ModelFunctionDefinition,
    ModelParameter,
    ModelReaction,
    ModelReactionItem,
    ModelFunction,
    Repositories\IEndpointRepository,
    Repositories\ModelReactionRepository};
use App\Exceptions\{DependentResourcesBoundException, MissingRequiredKeyException, WrongParentException};
use App\Helpers\ArgumentParser;
use Slim\Http\{
	Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read ModelReactionRepository $repository
 * @method ModelReaction getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ModelReactionController extends ParentedRepositoryController implements IGroupRoleAuthWritableController
{

    use SBaseControllerCommonable;


	protected static function getAllowedSort(): array
	{
		return ['id', 'name'];
	}

	protected function getData(IdentifiedObject $reaction): array
	{	    /** @var Model $model */
//        $model = $this->repository->getParent();
//        $defs = [];
//        $model->getFunctionDefinitions()->map(function (ModelFunctionDefinition $fnDef) use (&$defs) {
//            $defs[$fnDef->getAlias()] = $fnDef->getExpression()->getLatex();
//        });
//        foreach ($defs as $alias => $fn) {
//            $latex = is_null($reaction->getRate()) ? '' : $reaction->getRate()->getLatex();
//            $pattern = '/(?<=\\mathrm{' . addcslashes(addcslashes($alias, '_'),'\\') . '}\().*?(?=\))/s';
//            preg_match_all($pattern,$latex,$ci);
//            $broken = explode( '\mapsto', $fn);
//            preg_match_all('/(?<=\\\lambda\().*?(?=\))/s', $broken[0], $strVar);
//            $vars = explode(',', str_replace(' ', '', $strVar[0][0]));
//            foreach ($ci[0] as $strSubs){
//                dump($strSubs, $pattern);exit();
//                $subs = explode(',', $strSubs);
//                $i = 0;
//                foreach ($subs as $sub)
//                {
//                    $broken[1] = str_replace($vars[$i], $sub, $broken[1]);
//                    $i++;
//                }
//            }
//        }
		/** @var ModelReaction $reaction */
		$sBaseData = $this->getSBaseData($reaction);
		return array_merge($sBaseData, [
			'modelId' => $reaction->getModelId()->getId(),
			'compartmentId' => $reaction->getCompartmentId() ? $reaction->getCompartmentId()->getId() : null,
			'isReversible' => $reaction->getIsReversible(),
            'rate' => [
                'latex' => is_null($reaction->getRate()) ? '' : $reaction->getRate()->getLatex(),
                'cmml' => is_null($reaction->getRate()) ? '' : $reaction->getRate()->getContentMML()],
			'reactionItems' => $reaction->getReactionItems()->map(function (ModelReactionItem $reactionItem) {
				return ['id' => $reactionItem->getId(),
                    'name' => $reactionItem->getName(),
                    'alias' => $reactionItem->getAlias(),
                    'stoichiometry' => $reactionItem->getStoichiometry(),
                    'type' => $reactionItem->getType()];
			})->toArray(),
			'functions' => $reaction->getFunctions()->map(function (ModelFunction $function) {
				return ['id' => $function->getId(), 'name' => $function->getName()];
			})->toArray(),
			'parameters' => $reaction->getParameters()->map(function (ModelParameter $parameter) {
				return ['id' => $parameter->getId(), 'name' => $parameter->getName()];
			})->toArray()
		]);
	}

	protected function setData(IdentifiedObject $reaction, ArgumentParser $data): void
	{
		/** @var ModelReaction $reaction */
        $this->setSBaseData($reaction, $data);
		$reaction->getModelId() ?: $reaction->setModelId($this->repository->getParent());
		!$data->hasKey('compartmentId') ?: $reaction->setCompartmentId($data->getString('compartmentId'));
		!$data->hasKey('reversible') ?: $reaction->setIsReversible($data->getBool('reversible'));
		if ($data->hasKey('rate')) {
            $expr = $reaction->getRate();
            $expr->setContentMML($data->getString('rate'), true);
            $reaction->setExpression($expr);
        }
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
        $expr = new MathExpression();
        $reaction = new ModelReaction();
        $reaction->setRate($expr);
        return $reaction;
//		if (!$body->hasKey('isReversible'))
//			throw new MissingRequiredKeyException('isReversible');
	}

	protected function checkInsertObject(IdentifiedObject $reaction): void
	{
		/** @var ModelReaction $reaction */
		if ($reaction->getModelId() === null)
			throw new MissingRequiredKeyException('modelId');
		if ($reaction->getIsReversible() === null)
			throw new MissingRequiredKeyException('reversible');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		$specie = $this->getObject($args->getInt('id'));
		if (!$specie->getReactionItems()->isEmpty())
			throw new DependentResourcesBoundException('reactionItem');
		if (!$specie->getFunctions()->isEmpty())
			throw new DependentResourcesBoundException('function');
        $this->deleteAnnotations($args->getInt('id'));
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		$validatorArray = $this->getSBaseValidator();
		return new Assert\Collection(array_merge($validatorArray, [
			'reversible' => new Assert\Type(['type' => 'integer']),
			'rate' => new Assert\Type(['type' => 'string']),
		]));
	}

	protected static function getObjectName(): string
	{
		return 'reaction';
	}

	protected static function getRepositoryClassName(): string
	{
		return ModelReactionRepository::Class;
	}

	protected function getParentObjectInfo(): ParentObjectInfo
	{
	    return new ParentObjectInfo('model-id', Model::class);
	}

    protected function checkParentValidity(IdentifiedObject $parent, IdentifiedObject $child)
    {
        /** @var ModelReaction $child */
        if ($parent->getId() != $child->getModelId()->getId()) {
            throw new WrongParentException($this->getParentObjectInfo()->parentEntityClass, $parent->getId(),
                self::getObjectName(), $child->getId());
        }
    }
}
