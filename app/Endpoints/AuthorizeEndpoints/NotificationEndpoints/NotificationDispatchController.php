<?php


namespace App\Controllers;

use App\Entity\Authorization\Notification\MailNotification;
use App\Entity\Authorization\Notification\NotificationLog;
use App\Entity\Authorization\User;
use App\Entity\Authorization\UserGroup;
use App\Entity\Authorization\UserGroupToUser;
use App\Entity\EBase;
use App\Entity\Experiment;
use App\Entity\IdentifiedObject;
use App\Entity\Model;
use App\Entity\ModelFunction;
use App\Entity\SBase;
use App\Exceptions\MissingRequiredKeyException;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\ORMException;
use SocketIO;

class NotificationDispatchController implements EventSubscriber
{

    /**
     * User id of the user that caused the notification
     * @var $id
     */
    private $id;

    /**
     * Defined in settings.local.php, array for auth purposes
     * @var $auth
     */
    private $auth;

    /**
     * Associative array that defines where is the websocket server
     * @var mixed
     */
    private $sock;


    /**
     * @var MailNotification
     */
    private $mailer;

    /**
     * NotificationDispatchController constructor.
     * @param $id
     * @param $notificationSettings
     * @throws MissingRequiredKeyException
     */
    public function __construct($id, $notificationSettings)
    {
        $this->id = $id;
        $this->mailer = new MailNotification(
            $notificationSettings['mailer']['dsn'],
            $notificationSettings['mailer']['salt'],
            $notificationSettings['mailer']['client_srv_redirect']);
        $this->auth = $notificationSettings['socketSettings']['auth'];
        $this->sock = $notificationSettings['socketSettings']['route'];
    }

    /**
     * Event to create notification when a resource is updated
     * @param LifecycleEventArgs $args
     * @throws ORMException|MissingRequiredKeyException
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $className = get_class($args->getObject());
        if($className == User::class) {
            $this->mailAboutUpdated($args->getObject()->getEmail());
        }
        if ($this->isModelEntity($className) || $this->isExpEntity($className)) {
            $this->createNotification($args, 'Updated');
        }
    }

    /**
     * Event to create notification when a resource is deleted
     * @param LifecycleEventArgs $args
     * @throws ORMException|MissingRequiredKeyException
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $className = get_class($args->getObject());
        if($className == User::class) {
            $this->mailAboutDeletion($args->getObject()->getEmail());
        }
        if ($this->isModelEntity($className) || $this->isExpEntity($className)) {
           $this->createNotification($args, 'Deleted');
        }
    }

    /**
     * Event to create notification when new resource is inserted
     * @param LifecycleEventArgs $args
     * @throws ORMException
     * @throws MissingRequiredKeyException
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $className = get_class($args->getObject());
        if($className == User::class) {
            $this->mailer->sendConfirmationMail($args->getObject()->getEmail());
        }
        if ($this->isModelEntity($className) || $this->isExpEntity($className)) {
            $this->createNotification($args, 'Added');
        }
    }

    /**
     * Discover if the persisted object has model origin.
     * Most of the model entities share SBase trait, except one.
     * @param string $className
     * @return bool
     */
    private function isModelEntity(string $className): bool
    {
        return in_array(SBase::class, class_uses($className)) ||
            $className == ModelFunction::class;
    }

    /**
     * Discover if the persisted object has model origin.
     * All of the Experiment entities share EBase trait.
     * @param string $className
     * @return bool
     */
    private function isExpEntity(string $className): bool
    {
        return in_array(EBase::class, class_uses($className));
    }

    /**
     * Creates new notification object, inserts it into DB and sends it via websocket to users.
     * @param LifecycleEventArgs $args
     * @param string $method
     * @throws ORMException
     */
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
            'id' => $rootParent['id']]));
        $notification->setWhat(json_encode([
            'type' => current($how)['table'],
            'id' => $ent->getId()]));
    }

    /** This is where the emitter-client happens. Get the receivers, connect to a webSocket and send the json
     * @param NotificationLog $object
     * @param EntityManager $em
     * @throws mixed
     */
    protected function shootTheNotification(NotificationLog $object, EntityManager $em){
        $origin = json_decode($object->getWhichParent(), TRUE);
        /** @var Model|Experiment $identifiedObject */
        $originId = is_null($origin['id']) ? json_decode($object->getWhat(), TRUE)['id'] : $origin['id'];
        $identifiedObject = $em->find('App\Entity\\' . ucfirst($origin['type']), $originId);
        $receivers = $em->find(UserGroup::class, $identifiedObject->getGroupId())
            ->getUsers()->map(function (UserGroupToUser $groupLink) {
                $user = $groupLink->getUserId();
                return $user->getId();
            })->toArray();

        $client = new SocketIO($this->sock['host'], $this->sock['port'],$this->sock['path']. '/socket.io/EIO=3');
        //$client = new SocketIO('service.e-cyanobacterium.org', 443, '/socket.io/socket.io/EIO=3');
        $client->setProtocol(SocketIO::SSL_PROTOCOL);
        $client->setQueryParams([
            'token' => $client->setAuth($this->auth),
            //'id' => '8780',
            //'cid' => '344',
            'cmp' => 2339
        ]);

        $success = $client->emit('notification', json_encode($this->prepareTheNotification($object, $receivers)));

        //For testing
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
            Events::preRemove,
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


    //---------- Mails to user -------//

    /**
     * @param $receiver
     * @throws MissingRequiredKeyException
     */
    protected function mailAboutDeletion(string $receiver): void
    {
        $this->mailer
            ->sendNotificationEmail($receiver,
                'Notification about account deletion.', "Your account is deleted.");
    }

    /**
     * @param $receiver
     * @throws MissingRequiredKeyException
     */
    protected function mailAboutUpdated(string $receiver):void
    {
        $this->mailer
            ->sendNotificationEmail($receiver,
                'Your account information has been updated.',"Your CMP acc was updated.");
    }

}