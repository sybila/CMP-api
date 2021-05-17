<?php


namespace App\Entity;


use Slim\Http\Response;
use SimpleXMLElement;

/**
 * Class SBMLModel
 * @package App\Entity
 * @author Radoslav Doktor & Marek Havlik
 */
class SBMLModel
{
    /** @var Model $model */
    private $model;

    /** @var array|null */
    private $withDataset;

    /** model in SBML */
    private $sbml;

    /**
     * SBMLModel constructor.
     * @param Model $model
     * @param array|null $withDataset
     */
    public function __construct(Model $model, array $withDataset = null)
    {
        $this->model = $model;
        $this->withDataset = $withDataset;
    }

    /**
     * @param array|null $withDataset
     */
    public function setWithDataset(?array $withDataset): void
    {
        $this->withDataset = $withDataset;
    }

    public function produceSBML(Response $response): Response
    {
        //Prepare final sbml
        $finalSbml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>' . "<sbml></sbml>");
        $finalSbml->addAttribute('xmlns',"http://www.sbml.org/sbml/level3/version2/core");
        $finalSbml->addAttribute('level', '3');
        $finalSbml->addAttribute('version', "2");

        //Create model element
        $model = $finalSbml->addChild('model');
        $model->addAttribute("id", $this->model->getAlias());
        $model->addAttribute("name", $this->model->getName());
        $this->sbml = $model;

        //Create lists of entities
        $this->setUnitDefinitionsSBML();
        $this->setFunctionDefinitionsSBML();
        $this->setCompartmentsAndSpeciesSBML();
        $this->setParametersSBML();
        $this->setInitialAssignmentsSBML();
        $this->setRulesSBML();
        $this->setConstraintsSBML();
        $this->setReactionsSBML();
        $this->setEventsSBML();

        $response->getBody()->write($finalSbml->asXML());
        return $response->withStatus(200)->withHeader("Content-Type",'application/xml');
    }

    function setUnitDefinitionsSBML()
    {
        $uUnitList = $this->sbml->addChild('listOfUnitDefinitions');
    }

    function setFunctionDefinitionsSBML()
    {
        $fnList = $this->sbml->addChild('listOfFunctionDefinitions');
        $this->model->getFunctionDefinitions()->map(function (ModelFunctionDefinition $fnDef) use ($fnList) {
            $fn = $fnList->addChild('functionDefinition');
            $fn->addAttribute('id', $fnDef->getAlias());
            $fn->addAttribute('name',$fnDef->getName());
            $this->appendMathDOM($fnDef->getExpression(), $fn);
        });}

    function setCompartmentsAndSpeciesSBML()
    {
        $compList = $this->sbml->addChild('listOfCompartments');
        $specList = $this->sbml->addChild('listOfSpecies');
        $this->model->getCompartments()->map(function (ModelCompartment $cpt) use ($compList, $specList) {
            $c = $compList->addChild('compartment');
            $cAlias = $cpt->getAlias();
            $c->addAttribute('id', $cpt->getAlias());
            $c->addAttribute('name', $cpt->getName());
            $c->addAttribute('spatialDimensions', $cpt->getSpatialDimensions());
            if (is_null($this->withDataset)) {
                $c->addAttribute('size', $cpt->getDefaultValue());
            } else {
                $subs = $this->getDatasetSubstitution($cpt->getAlias());
                is_null($subs)
                    ? $c->addAttribute('size', $cpt->getDefaultValue())
                    : $c->addAttribute('size', $subs['initialValue']);
            }
            $c->addAttribute('constant', $cpt->getConstant() ? 'true' : 'false');
            $cpt->getSpecies()->map(function (ModelSpecie $spec) use ($specList, $cAlias) {
                $s = $specList->addChild('species');
                $s->addAttribute('id', $spec->getAlias());
                $s->addAttribute('name', $spec->getName());
                $s->addAttribute('compartment', $cAlias);
                if (is_null($this->withDataset)) {
                    $s->addAttribute('initialAmount', $spec->getDefaultValue());
                } else {
                    $subs = $this->getDatasetSubstitution($spec->getAlias());
                    is_null($subs)
                        ? $s->addAttribute('initialAmount', $spec->getDefaultValue())
                        : $s->addAttribute('initialAmount', $subs['initialValue']);
                }
                $s->addAttribute('hasOnlySubstanceUnits', $spec->getHasOnlySubstanceUnits());
                $s->addAttribute('boundaryCondition', $spec->getBoundaryCondition() ? 'true' : 'false');
                $s->addAttribute('constant', $spec->getConstant() ? 'true' : 'false');
            });
        });
    }

    function setParametersSBML()
    {
        $paraList = $this->sbml->addChild('listOfParameters');
        $this->model->getParameters()->map(function (ModelParameter $param) use ($paraList) {
            $p = $paraList->addChild('parameter');
            $p->addAttribute('id', $param->getAlias());
            $p->addAttribute('name', $param->getName());
            if (is_null($this->withDataset)) {
                $p->addAttribute('value', $param->getDefaultValue());
            } else {
                $subs = $this->getDatasetSubstitution($param->getAlias());
                is_null($subs)
                    ? $p->addAttribute('value', $param->getDefaultValue())
                    : $p->addAttribute('value', $subs['initialValue']);
            }
            is_null($param->getUnits()) ?: $p->addAttribute('units', $param->getUnits());
            $p->addAttribute('constant', $param->getConstant() ? 'true' : 'false');
        });
    }

    function setInitialAssignmentsSBML()
    {
        $initAssList = $this->sbml->addChild('listOfInitialAssignments');
        $this->model->getInitialAssignments()->map(function (ModelInitialAssignment $ass) use ($initAssList) {
            $initAss = $initAssList->addChild('initialAssignment');
            $initAss->addAttribute('symbol', $ass->getAlias());
            $this->appendMathDOM($ass->getExpression(), $initAss);
        });
    }

    function setRulesSBML()
    {
        $ruleList = $this->sbml->addChild('listOfRules');
        $this->model->getRules()->map(function (ModelRule $rul) use ($ruleList) {
            if ($rul->getType() === 'assignment') {
                $mr = $ruleList->addChild('assignmentRule');
                $mr->addAttribute('variable', $rul->getVariableAlias());
                $this->appendMathDOM($rul->getExpression(), $mr);
            }
            if ($rul->getType() === 'rate') {
                $mr = $ruleList->addChild('rateRule');
                $mr->addAttribute('variable', $rul->getVariableAlias());
                $this->appendMathDOM($rul->getExpression(), $mr);
            }
        });
    }

    function setReactionsSBML()
    {
        $reactList = $this->sbml->addChild('listOfReactions');
        $this->model->getReactions()->map(function (ModelReaction $reaction) use ($reactList) {
            $rc = $reactList->addChild('reaction');
            $rc->addAttribute('id', $reaction->getAlias());
            $rc->addAttribute('name', $reaction->getName());
            $rc->addAttribute('reversible', $reaction->getIsReversible() ? 'true' : 'false');
            $reactants = $rc->addChild('listOfReactants');
            $products = $rc->addChild('listOfProducts');
            $reaction->getReactionItems()->map(function (ModelReactionItem $rItem) use ($reactants, $products){
                if ($rItem->getType() === 'reactant') {
                    $rRef = $reactants->addChild('speciesReference');
                    $rRef->addAttribute('species', $rItem->getAlias());
                    $rRef->addAttribute('stoichiometry', $rItem->getStoichiometry());
                    $rRef->addAttribute('constant', $rItem->getConstant() ? 'true' : 'false');
                }
                if ($rItem->getType() === 'product') {
                    $pRef = $products->addChild('speciesReference');
                    $pRef->addAttribute('species', $rItem->getAlias());
                    $pRef->addAttribute('stoichiometry', $rItem->getStoichiometry());
                    $pRef->addAttribute('constant', $rItem->getConstant() ? 'true' : 'false');
                }
            });
            $kineticLaw = $rc->addChild('kineticLaw');
            $this->appendMathDOM($reaction->getRate(), $kineticLaw);
        });
    }

    function setEventsSBML()
    {
        $eventList = $this->sbml->addChild('listOfEvents');
        $this->model->getEvents()->map(function (ModelEvent $event) use ($eventList) {
            $sbmlExent = $eventList->addChild('event');
            $sbmlExent->addAttribute('id', $event->getAlias());
            $sbmlExent->addAttribute('name', $event->getName());
            $sbmlExent->addAttribute('useValuesFromTriggerTime', $event->getEvaluateOnTrigger());
            $trig = $sbmlExent->addChild('trigger');
            $trig->addAttribute('initialValue', $event->getEvaluateOnTrigger() ? 'true' : 'false');
            $trig->addAttribute('persistent', $event->getEvaluateOnTrigger() ? 'true' : 'false');
            $this->appendMathDOM($event->getTrigger(), $trig);
            $eAssList = $sbmlExent->addChild('listOfEventAssignments');
            $event->getEventAssignments()->map(function (ModelEventAssignment $eass) use ($eAssList) {
                $ass = $eAssList->addChild('eventAssignment');
                $ass->addAttribute('variable', $eass->getVariable()->getAlias());
                $this->appendMathDOM($eass->getFormula(), $ass);
            });
        });
    }

    private function setConstraintsSBML()
    {
        $constraintList = $this->sbml->addChild('listOfConstraints');
    }

    private function appendMathDOM(MathExpression $mathExpr, SimpleXMLElement $xmlElement)
    {
        $dom = dom_import_simplexml($xmlElement);
        $math = new SimpleXMLElement($mathExpr->getContentMML());
        $mathDom = dom_import_simplexml($math);
        $mathDom = $dom->ownerDocument->importNode($mathDom, TRUE);
        $dom->appendChild($mathDom);
    }

    private function getDatasetSubstitution(string $varAlias)
    {
            foreach ($this->withDataset as $var) {
                if ($var['alias'] === $varAlias) {
//                    if ($varAlias === 'R0') {
//                        dump($var);exit;
//                    }
                    return $var;
                }
            }
            return null;
    }

}