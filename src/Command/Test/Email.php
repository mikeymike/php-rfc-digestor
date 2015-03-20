<?php


namespace MikeyMike\RfcDigestor\Command\Test;

use Noodlehaus\Config;
use MikeyMike\RfcDigestor\Service\DiffService;
use MikeyMike\RfcDigestor\Service\RfcService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Email
 *
 * @package MikeyMike\RfcDigestor\Command\Test
 * @author  Michael Woodward <mikeymike.mw@gmail.com>
 */
class Email extends Command
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @param Config        $config
     * @param \Swift_Mailer $mailer
     */
    public function __construct(Config $config, \Swift_Mailer $mailer, \Twig_Environment $twig)
    {
        $this->config = $config;
        $this->mailer = $mailer;
        $this->twig   = $twig;

        parent::__construct();
    }

    /**
     * Configure Command
     */
    public function configure()
    {
        $this
            ->setName('test:email')
            ->setDescription('Test application SMTP settings')
            ->addArgument('email', InputArgument::REQUIRED, 'Email to notify');
    }

    /**
     * Execute Command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $this->twig->render('test.twig');

        $message = $this->mailer->createMessage()
            ->setSubject('PHP RFC Digestor Test Email')
            ->setFrom('notifier@php-rfc-digestor.com')
            ->setTo($input->getArgument('email'))
            ->setBody($email, 'text/html');

        $this->mailer->send($message);

        $output->writeln(sprintf('<info>Email sent to %s</info>', $input->getArgument('email')));
    }
}
