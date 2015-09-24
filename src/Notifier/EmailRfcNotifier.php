<?php

namespace MikeyMike\RfcDigestor\Notifier;

use MikeyMike\RfcDigestor\Entity\Rfc;
use Doctrine\Common\Persistence\ObjectRepository;
use Noodlehaus\Config;

/**
 * Class EmailRfcNotifier
 * @package MikeyMike\RfcDigestor\Notifier
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class EmailRfcNotifier implements RfcNotifierInterface
{
    /**
     * @var ObjectRepository
     */
    private $emailSubscriberRepository;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param ObjectRepository $emailSubscriberRepository
     * @param \Swift_Mailer $mailer
     * @param \Twig_Environment $twig
     * @param Config $config
     */
    public function __construct(
        ObjectRepository $emailSubscriberRepository,
        \Swift_Mailer $mailer,
        \Twig_Environment $twig,
        Config $config
    ) {
        $this->emailSubscriberRepository = $emailSubscriberRepository;
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->config = $config;
    }

    /**
     * @param Rfc $rfc
     * @param array $voteDiff
     */
    public function notify(Rfc $rfc, array $voteDiff)
    {
        foreach ($this->emailSubscriberRepository->findAll() as $subscriber) {
            $email = $this->twig->render('rfc.twig', [
                'rfcName'           => $rfc->getName(),
                'details'           => $voteDiff['details'],
                'changeLog'         => $voteDiff['changeLog'],
                'voteDiffs'         => $voteDiff['votes'],
                'rfcVotes'          => $rfc->getVotes(),
                'unsubscribeUrl'    => sprintf(
                    '%s/unsubscribe/%s',
                    $this->config->get('app.url'),
                    $subscriber->getUnsubscribeToken()
                )
            ]);

            $message = $this->mailer->createMessage()
                ->setSubject(sprintf('Re: %s updated!', $rfc->getName()))
                ->setFrom('notifier@php-rfc-digestor.com')
                ->setTo($subscriber->getEmail())
                ->setBody($email, 'text/html');

            $this->mailer->send($message);
        }
    }
}
