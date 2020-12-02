<?php


namespace App\Entity\Authorization\Notification;


use App\Exceptions\MissingRequiredKeyException;
use Symfony\Component\Mailer\Exception\InvalidArgumentException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

class MailNotification
{
    private $transport;

    private $authSalt;

    private $redirect;

    /**
     * MailNotification constructor.
     * @param $transport
     * @param $authSalt
     * @param $redirect
     * @throws MissingRequiredKeyException
     */
    public function __construct($transport, $authSalt, $redirect)
    {
        try {
            $this->transport = Transport::fromDsn($transport);
        }
        catch (InvalidArgumentException $e) {
            throw new MissingRequiredKeyException('dsn is not set up properly.');
        }
        $this->authSalt = $authSalt;
        $this->redirect = $redirect;
    }

    /**
     * @param $receiver
     * @param $subject
     * @param $message
     * @throws MissingRequiredKeyException
     */
    public function sendNotificationEmail($receiver, $subject, $message){
        $mailer = new Mailer($this->transport);
        $email = (new Email())
            ->from('ecyano@mail.muni.cz')
            ->to($receiver)
            ->subject('CMP:' . $subject)
            ->html("<p>$message</p>");
        try {
            $mailer->send($email);
        }
        catch (TransportExceptionInterface $e){
            throw new MissingRequiredKeyException($e->getMessage() . $e->getDebug());
        }
    }


    /**
     * @param $receiver
     * @throws MissingRequiredKeyException
     */
    public function sendConfirmationMail($receiver){
        $hash = sha1($receiver . $this->authSalt);
        $url = $_SERVER['REQUEST_SCHEME'] . '://' . $this->redirect . '/' . $receiver . '/' . $hash;
        $mailer = new Mailer($this->transport);
        $email = (new Email())
            ->from('ecyano@fi.muni.cz')
            ->to($receiver)
            ->subject('CMP: Confirm your registration')
            ->html("<p>If you want to fully activate your account click on <a href=$url>this link</a></p>");
        try {
            $mailer->send($email);
        }
        catch (TransportExceptionInterface $e){
            throw new MissingRequiredKeyException($e->getMessage() . $e->getDebug());
        }
    }

    /**
     * @return mixed
     */
    public function getAuthSalt()
    {
        return $this->authSalt;
    }

    /**
     * @return mixed
     */
    public function getRedirect()
    {
        return $this->redirect;
    }



}