<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use DOMDocument;

/**
 * @ORM\Entity
 * @ORM\Table(name="model_function_definition")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 * @author Radoslav Doktor & Marek Havlik
 */
class ModelFunctionDefinition implements IdentifiedObject
{
	use SBase;

	/**
	 * @ORM\ManyToOne(targetEntity="Model", inversedBy="compartments")
	 * @ORM\JoinColumn(name="model_id", referencedColumnName="id")
	 */
	protected $modelId;

    /**
     * @ORM\OneToOne(targetEntity="MathExpression", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="formula", referencedColumnName="id")
     */
	protected $expression;

    /**
     * @ORM\Column(type="string", name="argv")
     */
	protected $arguments;

	/**
	 * Get modelId
	 * @return integer|null
	 */
	public function getModelId()
	{
		return $this->modelId;
	}

    /**
     * @param $modelId
     */
	public function setModelId($modelId)
	{
		$this->modelId = $modelId;
	}


	public function getExpression()
	{
		return $this->expression;
	}

	public function setExpression(MathExpression $expression)
	{
		$this->expression = $expression;
		if ($expression !== null) {
            $dom = new DOMDocument;
            $dom->loadXML($expression->getContentMML());
            $args = [];
            foreach ($dom->getElementsByTagName('bvar') as $arg) {
                array_push($args, $arg->nodeValue);
            }
            $this->setArguments($args);
        }
	}

    /**
     * @return mixed
     */
    public function getArguments(): array
    {
        return json_decode($this->arguments);
    }

    /**
     * @param mixed $arguments
     */
    public function setArguments(array $arguments): void
    {
        $this->arguments = json_encode($arguments);
    }



}
