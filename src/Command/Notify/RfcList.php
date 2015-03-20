<?php


namespace MikeyMike\RfcDigestor\Command\Notify;

use MikeyMike\RfcDigestor\Service\DiffService;
use MikeyMike\RfcDigestor\Service\RfcService;
use Noodlehaus\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RfcList
 *
 * @package MikeyMike\RfcDigestor\Command
 * @author  Michael Woodward <mikeymike.mw@gmail.com>
 */
class RfcList extends Command
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var RfcService
     */
    protected $rfcService;

    /**
     * @var DiffService
     */
    protected $diffService;

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
     * @param RfcService    $rfcService
     * @param DiffService   $diffService
     * @param \Swift_Mailer $mailer
     */
    public function __construct(
        Config $config,
        RfcService $rfcService,
        DiffService $diffService,
        \Swift_Mailer $mailer,
        \Twig_Environment $twig
    ) {
        $this->config      = $config;
        $this->rfcService  = $rfcService;
        $this->diffService = $diffService;
        $this->mailer      = $mailer;
        $this->twig        = $twig;

        parent::__construct();
    }

    /**
     * Configure Command
     */
    public function configure()
    {
        $this
            ->setName('notify:list')
            ->setDescription('Get notifications of RFC list changes')
            ->addArgument('email', InputArgument::REQUIRED, 'Email to notify');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (posix_isatty(STDOUT)) {
            $output->writeln('<info>щ(ºДºщ) This command is pointless when not run on a cron</info>');
        }

        $currRfcList = $this->rfcService->getLists();
        $storageFile = sprintf('%s/rfcList.json', $this->config->get('storagePath'));

        if (!file_exists($storageFile)) {
            file_put_contents($storageFile, json_encode($currRfcList));
            return;
        }

        $prevRfcList = json_decode(file_get_contents($storageFile), true);
        $diffs       = $this->diffService->listDiff($currRfcList, $prevRfcList);

        if (empty($diffs)) {
            file_put_contents($storageFile, json_encode($currRfcList));
            return;
        }

        $email = $this->twig->render('list.twig', [
            'changes' => $diffs
        ]);

        $message = $this->mailer->createMessage()
            ->setSubject('Some RFCs have moved!')
            ->setFrom('notifier@php-rfc-digestor.com')
            ->setTo($input->getArgument('email'))
            ->setBody($email, 'text/html');

        $this->mailer->send($message);

        file_put_contents($storageFile, json_encode($currRfcList));
    }
}
