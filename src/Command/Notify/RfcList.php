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
     * @param RfcService $rfcService
     */
    public function __construct(Config $config, RfcService $rfcService, DiffService $diffService)
    {
        $this->config      = $config;
        $this->rfcService  = $rfcService;
        $this->diffService = $diffService;

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
     * Execute Command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (posix_isatty(STDOUT)) {
            $output->writeln('<info>щ(ºДºщ) This command is pointless when not run on a cron</info>');
        }

        $currentRfcList = $this->rfcService->getLists();
        $storageFile    = sprintf('%s/rfcList.json', $this->config->get('storagePath'));

        if (!file_exists($storageFile)) {
            $this->writeRfcFile($currentRfcList, $storageFile);
            return;
        }

        $previousRfcList = json_decode(file_get_contents($storageFile), true);

        $diffs = $this->diffService->listDiff($currentRfcList, $previousRfcList);

        // TODO : Twig template

        $emailBody = '';

        foreach ($diffs as $rfcTitle => $diff) {
            $emailBody .= sprintf("%s has moved from %s to %s\n", $rfcTitle, $diff['from'], $diff['to']);
        }

        $mailer = new \Swift_Mailer(new \Swift_MailTransport());

        $message = $mailer->createMessage()
            ->setSubject('Test')
            ->setFrom('notifier@php-rfc-digestor.com')
            ->setTo($input->getArgument('email'))
            ->setBody($emailBody);

        $mailer->send($message);

        // $this->writeRfcFile($currentRfcList, $file);
    }

    /**
     * @param $rfcList array
     */
    public function writeRfcFile($rfcList, $file)
    {
        if (!is_array($rfcList)) {
            throw new RuntimeException('Cannot write rfc list to file');
        }

        file_put_contents($file, json_encode($rfcList));
    }
}
