<?php

namespace App\Controllers;

use App\Entity\Authorization\Notification\NotificationLog;
use App\Entity\IdentifiedObject;
use App\Repositories\Authorization\NotificationLogRepository;

class NotificationLogController extends RepositoryController
{

    protected static function getAllowedSort(): array
    {
        return ['when'];
    }

    protected static function getRepositoryClassName(): string
    {
        return NotificationLogRepository::class;
    }

    protected static function getObjectName(): string
    {
        return NotificationLog::class;
    }

    protected function getData(IdentifiedObject $object): array
    {
        $how = json_decode($object->getHow(), true);
        /** @var NotificationLog $object */
        return [
            "who" => $object->getWhoId(),
            "what" => json_decode($object->getWhat()),
            "origin" => json_decode($object->getWhichParent()),
            "when"=> json_decode($object->getWhen()),
            "how" => [
                'method' => $how['method'],
                'data' => json_decode($how['data'])
            ]
        ];
    }
}