<?php


namespace App\Controllers;


use App\Entity\AnnotableObjectType;
use App\Entity\IdentifiedObject;
use App\Entity\MathExpression;
use App\Entity\Model;
use App\Entity\ModelCompartment;
use App\Entity\ModelEvent;
use App\Entity\ModelEventAssignment;
use App\Entity\ModelFunctionDefinition;
use App\Entity\ModelInitialAssignment;
use App\Entity\ModelParameter;
use App\Entity\ModelReaction;
use App\Entity\ModelReactionItem;
use App\Entity\ModelRule;
use App\Entity\ModelSpecie;
use App\Exceptions\InvalidTypeException;
use App\Exceptions\MissingRequiredKeyException;
use App\Exceptions\NonExistingObjectException;
use App\Helpers\ArgumentParser;
use Doctrine\ORM\EntityManager;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Symfony\Component\Validator\Constraints as Assert;

class ImportModelController extends WritableRepositoryController
{

    /** @var ModelController $modelCtl */
    protected $modelCtl;

    /** @var AnnotationSourceController $annCtl */
    protected $annCtl;

    /** @var ModelCompartmentController $compCtl */
    protected $compCtl;

    /** @var ModelSpecieController $specCtl */
    protected $specCtl;

    /** @var ModelParentedRuleController $ruleCtl */
    protected $ruleCtl;

    /** @var ModelReactionController $reactCtl */
    protected $reactCtl;

    /** @var ModelParentedParameterController $paraCtl */
    protected $paraCtl;

    /** @var ReactionItemParentedParameterController $localParaCtl */
    protected $localParaCtl;

    /** @var ReactionParentedReactionItemController $rItemCtl */
    protected $rItemCtl;

    /** @var ModelConstraintController */
    protected $constCtl;

    /** @var ModelInitialAssignmentController */
    protected $initAssCtl;

    /** @var ModelEventController $eventCtl */
    protected $eventCtl;

    /** @var ModelEventAssignmentController $eventAssCtl */
    protected $eventAssCtl;

    /** @var ModelFunctionDefinitionController $fnDefCtl */
    protected $fnDefCtl;

    protected $compartments = [];
    protected $species = [];
    protected $parameters = [];


    /**
     * ImportModelController constructor.
     * @param Container $c
     */
    public function __construct(Container $c)
    {
        //parent::__construct($c);
        $this->orm = $c->get(EntityManager::class);
        $this->modelCtl = new ModelController($c);
        $this->annCtl = new AnnotationSourceController($c);
        $this->compCtl = new ModelCompartmentController($c);
        $this->specCtl = new ModelSpecieController($c);
        $this->ruleCtl = new ModelParentedRuleController($c);
        $this->reactCtl = new ModelReactionController($c);
        $this->paraCtl = new ModelParentedParameterController($c);
        $this->localParaCtl = new ReactionItemParentedParameterController($c);
        $this->rItemCtl = new ReactionParentedReactionItemController($c);
        $this->constCtl = new ModelConstraintController($c);
        $this->eventCtl = new ModelEventController($c);
        $this->eventAssCtl = new ModelEventAssignmentController($c);
        $this->fnDefCtl = new ModelFunctionDefinitionController($c);
        $this->initAssCtl = new ModelInitialAssignmentController($c);
    }

    protected static function getAllowedSort(): array
    {
        return [];
    }

    protected static function getRepositoryClassName(): string
    {
        return '';
    }

    protected static function getObjectName(): string
    {
        return '';
    }

    protected function getData(IdentifiedObject $object): array
    {
        return [];
    }

    public function dumpTest($response, $anything)
    {
        return self::formatOk($response, [$anything]);
    }

    public function add(Request $request, Response $response, ArgumentParser $args): Response
    {
        $this->setUserPermissions($request->getAttribute('oauth_user_id'));
        $this->permitUser([$this, 'validateAdd'], [$this, 'canAdd']);
        $wholeModel = $request->getParsedBody()['data'];
        $id = $this->importModel($wholeModel);

//        $this->permitUser([$this, 'validateAdd'], [$this, 'canAdd']);
//        $body = new ArgumentParser($request->getParsedBody());
//        $this->validate($body, $this->getValidator());
//        $object = $this->createObject($body);
//        $this->setData($object, $body);
//        $this->checkInsertObject($object);
//
        //$this->runEvents($this->beforeInsert, $object);

//
//        $this->orm->persist($object);
//
//        //FIXME: flush shouldn't be called here but in FlushMiddleware, but then we can't get inserted object id
//        $this->orm->flush();
        return self::formatInsert($response, $id);
    }

    protected function importModel(array $model)
    {
        $modelData = new ArgumentParser($model);
        /** @var Model $modelObj */
        $modelObj = $this->modelCtl->createObject($modelData);
        $modelObj->setUserId($this->userPermissions['user_id']);
        $modelObj->setGroupId(array_key_first($this->userPermissions['group_wise']));
        //$this->modelCtl->checkInsertObject($modelObj);
        $this->modelCtl->setData($modelObj, $modelData);

        $this->orm->persist($modelObj);
        $this->orm->flush();
        is_null($modelData['annotations']) ?: $this->importAnnotation(Model::class,$modelObj, $modelData['annotations']);

        if ($modelData->hasKey('compartments')) {
            $this->compCtl->repository->setParent($modelObj);
            $this->importCompartments($modelData['compartments']);
        }
        if ($modelData->hasKey('species')) {
            $this->importSpecies($modelData['species']);
        }
        if ($modelData->hasKey('reactions')) {
            $this->reactCtl->repository->setParent($modelObj);
            $this->importReactions($modelData['reactions']);
        }
        if ($modelData->hasKey('parameters')) {
            $this->paraCtl->repository->setParent($modelObj);
            $this->importParameters($modelData['parameters']);
        }
        if ($modelData->hasKey('rules')) {
            $this->ruleCtl->repository->setParent($modelObj);
            $this->importRules($modelData['rules']);
        }
        if ($modelData->hasKey('functionDefinitions')) {
            $this->fnDefCtl->repository->setParent($modelObj);
            $this->importFunctionDefinitions($modelData['functionDefinitions']);
        }
        if ($modelData->hasKey('unitDefinitions')) {

        }
        if ($modelData->hasKey('initialAssignments')) {
            $this->initAssCtl->repository->setParent($modelObj);
            $this->importInitAssignment($modelData['initialAssignments']);
        }
//        if($modelData->hasKey('constraints')) {
//            $this->constCtl->repository->setParent($modelObj);
//        }
        if($modelData->hasKey('events')) {
            $this->eventCtl->repository->setParent($modelObj);
            $this->importEvents($modelData['events']);
        }
        return $modelObj->getId();
    }

    protected function importAnnotation(string $objName, IdentifiedObject $obj, ?array $annData)
    {
        if (!empty($annData)) {
            $this->annCtl->repository->setParent($obj);
            $this->annCtl->setAnnotatedObjType($objName);
            foreach ($annData as $ann) {
                $annInfo = new ArgumentParser($ann);
                $annotation = $this->annCtl->createObject($annInfo);
                $this->annCtl->setData($annotation, $annInfo);
                $this->orm->persist($annotation);
                $this->orm->flush();
            }
        }
    }

    protected function importCompartments(array $comps)
    {
        foreach ($comps as $cpart) {
            $compData = new ArgumentParser($cpart);
            /** @var ModelCompartment $compartment */
            $compartment = $this->compCtl->createObject($compData);
            $this->compCtl->setData($compartment, $compData);
            $this->orm->persist($compartment);
            $this->orm->flush();
            is_null($compData['annotations']) ?: $this->importAnnotation(ModelCompartment::class, $compartment, $compData['annotations']);
            $this->compartments[$compartment->getAlias()] = $compartment;
        }
    }

    protected function importSpecies(array $species)
    {
        foreach ($species as $spec) {
            $specData = new ArgumentParser($spec);
            !$specData->hasKey('compartment') ?:
                $this->specCtl->repository->setParent($this->compartments[$specData['compartment']]);
            /** @var ModelSpecie $speciesObj */
            $speciesObj = $this->specCtl->createObject($specData);
            $this->specCtl->setData($speciesObj, $specData);
            $this->orm->persist($speciesObj);
            $this->orm->flush();
            is_null($specData['annotations']) ?: $this->importAnnotation(ModelSpecie::class, $speciesObj, $specData['annotations']);
            $this->species[$speciesObj->getAlias()] = $speciesObj;
        }
    }

    protected function importRules(array $rules)
    {
        $i = 1;
        //FIXME type in rule obj
        foreach ($rules as $rule) {
            $parentType = ['parameter' => false, 'species' => false, 'compartment' => false];
            $ruleData = new ArgumentParser($rule);
            if (array_key_exists($ruleData['variable'], $this->parameters)) {
                /** @var ModelParameter $parentPar */
                $parentPar = $this->parameters[$ruleData['variable']];
                $parentType['parameter'] = true;
            } elseif (array_key_exists($ruleData['variable'], $this->compartments)) {
                /** @var ModelCompartment $parentComp */
                $parentComp = $this->compartments[$ruleData['variable']];
                $parentType['compartment'] = true;
            } elseif (array_key_exists($ruleData['variable'], $this->species)) {
                /** @var ModelSpecie $parentSpec */
                $parentSpec = $this->species[$ruleData['variable']];
                $parentType['species'] = true;
            }
            /** @var ModelRule $ruleObj */
            $ruleObj = $this->ruleCtl->createObject($ruleData);
            !$parentType['parameter'] ?: $ruleObj->setParameterId($parentPar->getId());
            !$parentType['species'] ?: $ruleObj->setSpecieId($parentSpec->getId());
            !$parentType['compartment'] ?: $ruleObj->setCompartmentId($parentComp);
            //user should be notified about this?
            $ruleData->hasKey('alias') ? $ruleObj->setAlias($ruleData['alias']) : $ruleObj->setAlias('rule' . $i);
            $i++;
            //
            $this->ruleCtl->setData($ruleObj, $ruleData);
            $this->orm->persist($ruleObj);
            $this->orm->flush();
            !$ruleData->hasKey('annotations') ?: $this->importAnnotation(ModelRule::class,$ruleObj, $ruleData['annotations']);
        }
    }

    protected function importReactions(array $reactions)
    {
        foreach ($reactions as $reaction) {
            $reactionData = new ArgumentParser($reaction);
            /** @var ModelReaction $reactionObj */
            $reactionObj = $this->reactCtl->createObject($reactionData);
            if ($reactionData->hasKey('kineticLaw')) {
                if (!is_null($reactionData['kineticLaw']['expression'])) {
                    $expr = $reactionObj->getRate();
                    $expr->setContentMML($reactionData['kineticLaw']['expression'], true);
                    $reactionObj->setRate($expr);
                }
                is_null($reactionData['kineticLaw']['parameters']) ?: $this->importParameters($reactionData['kineticLaw']['parameters']);
            }
            $this->reactCtl->setData($reactionObj, $reactionData);
            $this->orm->persist($reactionObj);
            $this->orm->flush();
            if ($reactionData->hasKey('reactants')) {
                $this->importReactionItems($reactionData['reactants'], 'reactant',$reactionObj);
            }
            if ($reactionData->hasKey('products')) {
                $this->importReactionItems($reactionData['products'], 'product',$reactionObj);
            }
            if ($reactionData->hasKey('modifiers')) {
                $this->importReactionItems($reactionData['modifiers'], 'modifier',$reactionObj);
            }
            !$reactionData->hasKey('annotations') ?: $this->importAnnotation(ModelReaction::class,$reactionObj, $reactionData['annotations']);
        }
    }

    protected function importParameters(array $parameters)
    {
        foreach ($parameters as $parameter) {
            $parameterData = new ArgumentParser($parameter);
            /** @var ModelParameter $parameterObj */
            $parameterObj = $this->paraCtl->createObject($parameterData);
            $this->paraCtl->setData($parameterObj, $parameterData);
            $this->orm->persist($parameterObj);
            $this->orm->flush();
            !$parameterData->hasKey('annotations') ?: $this->importAnnotation(ModelParameter::class, $parameterObj, $parameterData['annotations']);
            $this->parameters[$parameterObj->getAlias()] = $parameterObj;
        }
    }

    /**
     * Reaction item is any specie/parameter that plays a role in a reaction.
     * Every corresponding item should point to a reaction.
     * @param array $rItems
     * @param string $itemType
     * @param IdentifiedObject $reactionObj
     */
    protected function importReactionItems(array $rItems, string $itemType,IdentifiedObject $reactionObj)
    {
        foreach ($rItems as $reactionItem) {
            $reactionItemData = new ArgumentParser($reactionItem);
            $reactionItemObj = new ModelReactionItem;
            $reactionItemObj->setType($itemType);
            $reactionItemObj->setReactionId($reactionObj);
            if($reactionItemData->hasKey('species')){
                $reactionItemObj->setSpecieId($this->species[$reactionItemData['species']]);
                $reactionItemObj->setAlias($reactionItemData['species']);
                $reactionItemObj->setName($reactionItemData['species']);
            }
            !$reactionItemData->hasKey('value') ?:
                $reactionItemObj->setValue($reactionItemData->getString('value'));
            !$reactionItemData->hasKey('stoichiometry') ?:
                $reactionItemObj->setStochiometry($reactionItemData->getString('stoichiometry'));
            !$reactionItemData->hasKey('notes') ?:
                $reactionItemObj->setNotes($reactionItemData->getString('notes'));
            //$this->rItemCtl->setData($reactionItemObj, $reactionItemData);
            $this->orm->persist($reactionItemObj);
            $this->orm->flush();
            !$reactionItemData->hasKey('annotations') ?: $this->importAnnotation(ModelReactionItem::class, $reactionItemObj, $reactionItemData['annotations']);

        }
    }

    protected function importFunctionDefinitions(array $fDefs)
    {
        foreach ($fDefs as $fnDef) {
            $fnDefData = new ArgumentParser($fnDef);
            $fnDefObj = $this->fnDefCtl->createObject($fnDefData);
            $this->fnDefCtl->setData($fnDefObj, $fnDefData);
            $this->orm->persist($fnDefObj);
            $this->orm->flush();
            !$fnDefData->hasKey('annotations') ?: $this->importAnnotation(ModelFunctionDefinition::class, $fnDefObj, $fnDefData['annotations']);
        }
    }

    private function importInitAssignment($initialAssignments)
    {
        foreach ($initialAssignments as $initAss) {
            $initAssData = new ArgumentParser($initAss);
            /** @var ModelInitialAssignment $initAssObj */
            $initAssObj = $this->initAssCtl->createObject($initAssData);
            $this->initAssCtl->setData($initAssObj, $initAssData);
            !$initAssData->hasKey('symbol') ?: $initAssObj->setAlias($initAssData->getString('symbol'));
            $this->orm->persist($initAssObj);
            $this->orm->flush();
            !$initAssData->hasKey('annotations') ?: $this->importAnnotation(ModelInitialAssignment::class,$initAssObj, $initAssData['annotations']);
        }
    }

    private function importEvents($events)
    {
        foreach ($events as $event) {
            $eventData = new ArgumentParser($event);
            /** @var ModelEvent $eventObj */
            $eventObj = $this->eventCtl->createObject($eventData);
            if ($eventData->hasKey('trigger')){
                if (!is_null($event['trigger']['expression'])) {
                    $trigger = new MathExpression();
                    $trigger->setContentMML($event['trigger']['expression'], true);
                    $eventObj->setTrigger($trigger);
                }
            }
            if ($eventData->hasKey('priority')){
                if (!is_null($event['priority']['expression'])) {
                    $priority = new MathExpression();
                    $priority->setContentMML($event['priority']['expression'], true);
                    $eventObj->setPriority($priority);
                }
            }
            if ($eventData->hasKey('delay')){
                if (!is_null($event['delay']['expression'])) {
                    $formula = new MathExpression();
                    $formula->setContentMML($event['delay']['expression'], true);
                    $eventObj->setDelay($formula);
                }
            }
            !$eventData->hasKey('alias') ?: $eventObj->setAlias($eventData->getString('alias'));
            !$eventData->hasKey('name') ?: $eventObj->setAlias($eventData->getString('name'));
            $eventObj->setModelId($this->eventCtl->repository->getParent());
            $eventData->hasKey('useValuesFromTriggerTime') ?
                $eventObj->setEvaluateOnTrigger($eventData['useValuesFromTriggerTime']) :
                $eventObj->setEvaluateOnTrigger(true);
            $this->orm->persist($eventObj);
            $this->orm->flush();
            !$eventData->hasKey('annotations') ?: $this->importAnnotation(ModelEvent::class, $eventObj, $eventData['annotations']);

            if ($eventData->hasKey('eventAssignments')) {
                $this->eventAssCtl->repository->setParent($eventObj);
                $this->importEventAss($eventData['eventAssignments']);
            }
        }
    }

    private function importEventAss($eventAssignments)
    {
        foreach ($eventAssignments as $eventAss){
            $eventAssData = new ArgumentParser($eventAss);
            /** @var ModelEventAssignment $eventAssObj */
            $eventAssObj = $this->eventAssCtl->createObject($eventAssData);
            $this->eventAssCtl->setData($eventAssObj, $eventAssData);
            if ($eventAssData->hasKey('expression')) {
                $formula = new MathExpression();
                $formula->setContentMML($eventAssData['expression'], true);
                $eventAssObj->setFormula($formula);
            }
            $this->orm->persist($eventAssObj);
            $this->orm->flush();
            !$eventAssData->hasKey('annotations') ?: $this->importAnnotation(ModelEventAssignment::class,$eventAssObj, $eventAssData['annotations']);
        }
    }

    private function insertMaths($mathml)
    {

    }

    protected function setData(IdentifiedObject $object, ArgumentParser $body): void
    {
        // TODO: Implement setData() method.
    }

    protected function createObject(ArgumentParser $body): IdentifiedObject
    {
        // TODO: Implement createObject() method.
    }

    protected function checkInsertObject(IdentifiedObject $object): void
    {
        // TODO: Implement checkInsertObject() method.
    }

    protected function getValidator(): Assert\Collection
    {
        // TODO: Implement getValidator() method.
    }

}