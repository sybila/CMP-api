<?php

use App\Entity\IdentifiedObject;
use App\Entity\SBase;
use App\Helpers\ArgumentParser;
use Symfony\Component\Validator\Constraints as Assert;

trait SBaseController
{
    protected function getSBaseData(IdentifiedObject $object): array
    {
        /** @var SBase $object*/
        return [
            'id' => $object->getId(),
            'name' => $object->getName(),
            'sbmlId' => $object->getSbmlId(),
            'sboTerm' => $object->getSboTerm(),
            'notes' => $object->getNotes(),
            'annotation' => $object->getAnnotation()
        ];
    }

    protected function setSBaseData(IdentifiedObject $object, ArgumentParser $body): void
    {
        /** @var SBase $object */
        !$body->hasKey('name') ? $object->setName($body->getString('sbmlId')) : $object->setName($body->getString('name'));
        !$body->hasKey('sbmlId') ?: $object->setSbmlId($body->getString('sbmlId'));
        !$body->hasKey('sboTerm') ?: $object->setSboTerm($body->getString('sboTerm'));
        !$body->hasKey('notes') ?: $object->setNotes($body->getString('notes'));
        !$body->hasKey('annotation') ?: $object->setAnnotation($body->getString(('annotation')));
    }

    protected function getSBaseValidator(): array
    {
        return [
            'name' => new Assert\Type(['type' => 'string']),
            'sbmlId' => new Assert\Type(['type' => 'string']),
            'sboTerm' => new Assert\Type(['type' => 'string']),
            'notes' => new Assert\Type(['type' => 'string']),
            'annotation' => new Assert\Type(['type' => 'string']),
        ];
    }
}