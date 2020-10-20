<?php

use App\Entity\AnalysisBase;
use App\Entity\IdentifiedObject;
use App\Exceptions\InvalidTypeException;
use App\Helpers\ArgumentParser;
use Symfony\Component\Validator\Constraints as Assert;

trait AnalysisControllerCommonable
{
    protected function getCommonAnalysisData(IdentifiedObject $object)
    {
        /** @var AnalysisBase $object */
        return [
            'id' => $object->getId(),
            'name' => $object->getName(),
            'description' => $object->getDescription(),
            'annotation' => $object->getAnnotation()
        ];
    }

    /**
     * @param IdentifiedObject $object
     * @param ArgumentParser $body
     * @throws InvalidTypeException
     */
    protected function setCommonAnalysisData(IdentifiedObject $object, ArgumentParser $body): void
    {
        /** @var AnalysisBase $object */
        if ($body->hasKey('name'))
            $object->setName($body->getString('name'));
        if ($body->hasKey('description'))
            $object->setDescription($body->getString('description'));
        if ($body->hasKey('annotation'))
            $object->setAnnotation($body->getString('annotation'));
    }

    protected function getCommonAnalysisDataValidator(): array
    {
        return [
            'name' => new Assert\Type(['type' => 'string']),
            'description' => new Assert\Type(['type' => 'string']),
            'annotation' => new Assert\Type(['type' => 'string']),
        ];
    }
}