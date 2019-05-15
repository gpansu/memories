<?php

// src/Service/MailService.php
namespace App\Service;

use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class MailService
{
    private $transport;
    private $mailer_from;
    private $mailer_from_no_reply;

    /**
     * SecurityService constructor.
     * @param String $mailer_host
     * @param String $mailer_port
     * @param String $mailer_user
     * @param String $mailer_password
     * @param String $mailer_from
     * @param String $mailer_from_no_reply
     */
    public function __construct(String $mailer_host, String $mailer_port, String $mailer_user, String $mailer_password, String $mailer_from, String $mailer_from_no_reply)
    {
        $this->mailer_from = $mailer_from;
        $this->mailer_from_no_reply = $mailer_from_no_reply;
        // Create the Transport
        $this->transport = (new Swift_SmtpTransport($mailer_host, $mailer_port))
            ->setUsername($mailer_user)
            ->setPassword($mailer_password);
    }


    # get success response from recaptcha and return it to controller
    function sendMail(String $subject, String $body, array $to)
    {

        // Create the Mailer using your created Transport
        $mailer = new Swift_Mailer($this->transport);

        // Create a message
        $message = (new Swift_Message($subject))
            ->setFrom([$this->mailer_from => 'Papigato.eu team'])
            ->setTo($to)
            ->setBody($body);

        // Send the message
        return $mailer->send($message);
    }

    # get success response from recaptcha and return it to controller
    function sendMailNoReply(String $subject, String $body, array $to)
    {

        // Create the Mailer using your created Transport
        $mailer = new Swift_Mailer($this->transport);

        // Create a message
        $message = (new Swift_Message($subject))
            ->setFrom([$this->mailer_from_no_reply => 'Papigato.eu noreply'])
            ->setTo($to)
            ->setBody(nl2br($body), 'text/html');

        // Send the message
        return $mailer->send($message);
    }
}