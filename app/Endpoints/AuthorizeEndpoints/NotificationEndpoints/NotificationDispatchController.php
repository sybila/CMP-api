<?php


namespace App\Controllers;


use App\Entity\Authorization\Notification\NotificationLog;
use App\Entity\IdentifiedObject;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Ratchet\WebSocket\WsServer;
use function Ratchet\Client\connect;

class NotificationDispatchController implements EventSubscriber
{

    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Event to crete notification when a resource is updated
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->createNotification($args, 'Updated');
    }

    /**
     * Event to crete notification when a resource is deleted
     * @param LifecycleEventArgs $args
     */
    public function postDelete(LifecycleEventArgs $args)
    {
        $this->createNotification($args, 'Deleted');
    }

    /**
     * Event to crete notification when new resource is inserted
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        if (! $args->getObject() instanceof NotificationLog) {
            $this->createNotification($args, 'Added');
        }
    }

    protected function createNotification(LifecycleEventArgs $args, string $method)
    {
        $notification = new NotificationLog();
        $em = $args->getEntityManager();
        $how = $em->getConfiguration()->getSQLLogger()->queries;
        /** @var IdentifiedObject $ent */
        $ent = $args->getEntity();
        $this->setData($notification, $how, $ent, $method);
        $em->persist($notification);
        $em->flush();
        $this->shootTheNotification($notification);
    }

    protected function setData(IdentifiedObject $notification, array $how, IdentifiedObject $ent, $method): void
    {
        /** @var NotificationLog $notification */
        $notification->setWhen();
        $notification->setWhoId($this->id);
        $notification->setHow(json_encode([
            'method' => $method,
            'data' => current($how)['data']]));
        $rootParent = $this->getRootParent();
        $notification->setWhichParent(json_encode([
            'type' => $rootParent['type'],
            'id' => (int)$rootParent['id']]));
        $notification->setWhat(json_encode([
            'type' => current($how)['table'],
            'id' => $ent->getId()]));
    }

    /** Connect to a webSocket and send the json
     * @param NotificationLog $object
     */
    protected function shootTheNotification(NotificationLog $object){
        $jsonMsg = $this->prepareTheNotification($object);
        connect('ws://localhost:8080')->then(function($conn) use ($jsonMsg) {
            //TODO send the secret first
            $conn->send($jsonMsg);
            $conn->close();
        }, function ($e) {
            dump("pozor");exit;
        });
    }

    /**
     * Prepare the notification
     * @param NotificationLog $object
     * @return string
     */
    protected function prepareTheNotification(NotificationLog $object): string
    {
        $how = json_decode($object->getHow(), true);
        $data = [
            "who" => $object->getWhoId(),
            "what" => json_decode($object->getWhat()),
            "origin" => json_decode($object->getWhichParent()),
            "when"=> json_decode($object->getWhen()),
            "how" => [
                'method' => $how['method'],
                'data' => json_decode($how['data'])
            ]
        ];
        return json_encode(["type" => "notification",
            "id" => 1,
            "data" => $data]);
    }

    public function getSubscribedEvents()
    {
        return [Events::postUpdate,
            Events::postRemove,
            Events::postPersist];
    }

    /**
     * @return array with root parent name and id
     */
    protected function getRootParent()
    {
        $split = array_diff(explode("/" , $_SERVER['REQUEST_URI']), explode("/", $_SERVER['SCRIPT_NAME']));
        return ['type' => array_shift($split), 'id' => array_shift($split)];
    }
}