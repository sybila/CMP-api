<?php


namespace App\Controllers;


use App\Entity\Authorization\Notification\NotificationLog;
use App\Entity\Authorization\UserGroup;
use App\Entity\Authorization\UserGroupToUser;
use App\Entity\Experiment;
use App\Entity\IdentifiedObject;
use App\Entity\Model;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use SocketIO;
use function Ratchet\Client\connect;

class NotificationDispatchController implements EventSubscriber
{

    private $id;
    private $auth;

    public function __construct($id, $auth)
    {
        $this->id = $id;
        $this->auth = $auth;
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
        //$em->flush();

        $this->shootTheNotification($notification, $em);
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
            'type' => substr($rootParent['type'], 0, -1),
            'id' => (int)$rootParent['id']]));
        $notification->setWhat(json_encode([
            'type' => current($how)['table'],
            'id' => $ent->getId()]));
    }

    /** This is our client. Get the receivers, connects to a webSocket and send the json
     * @param NotificationLog $object
     * @param EntityManager $em
     * @throws mixed
     */
    protected function shootTheNotification(NotificationLog $object, EntityManager $em){
        $origin = json_decode($object->getWhichParent(), TRUE);

        /** @var Model|Experiment $identifiedObject */
        $identifiedObject = $em->find('App\Entity\\' . ucfirst($origin['type']), $origin['id']);
        $receivers = $em->find(UserGroup::class, $identifiedObject->getGroupId())
            ->getUsers()->map(function (UserGroupToUser $groupLink) {
                $user = $groupLink->getUserId();
                return $user->getId();
            })->toArray();

        $client = new SocketIO('localhost', 9001);
        $client->setQueryParams([
            'token' => $client->setAuth($this->auth),
            //'id' => '8780',
            //'cid' => '344',
            'cmp' => 2339
        ]);

        $success = $client->emit('notification', json_encode($this->prepareTheNotification($object, $receivers)));

        if(!$success)
        {
            var_dump($client->getErrors());
        }
        else {
            var_dump("Success");
        }
    }

    /**
     * Prepare the notification
     * @param NotificationLog $object
     * @param array $toIds
     * @return array
     */
    protected function prepareTheNotification(NotificationLog $object, array $toIds): array
    {
        $how = json_decode($object->getHow(), true);
        return ["ids" => $toIds,
                "data" => [
                "who" => $object->getWhoId(),
                "what" => json_decode($object->getWhat()),
                "origin" => json_decode($object->getWhichParent()),
                "when"=> json_decode($object->getWhen()),
                "how" => [
                    'method' => $how['method'],
                    'data' => json_decode($how['data'])
                ]
            ]
        ];
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