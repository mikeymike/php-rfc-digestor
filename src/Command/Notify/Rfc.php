<?php


namespace MikeyMike\RfcDigestor\Command\Notify;

use MikeyMike\RfcDigestor\Service\RfcService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Rfc
 *
 * @package MikeyMike\RfcDigestor\Command
 * @author  Michael Woodward <mikeymike.mw@gmail.com>
 */
class Rfc extends Command
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
     * @param Config        $config
     * @param RfcService    $rfcService
     * @param DiffService   $diffService
     * @param \Swift_Mailer $mailer
     */
    public function __construct(Config $config, RfcService $rfcService, DiffService $diffService, \Swift_Mailer $mailer)
    {
        $this->config      = $config;
        $this->rfcService  = $rfcService;
        $this->diffService = $diffService;
        $this->mailer      = $mailer;

        parent::__construct();
    }

    /**
     * Configure Command
     */
    public function configure()
    {
        $this
            ->setName('notify:rfc')
            ->setDescription('Get notifications of RFC changes')
            ->addArgument('rfc', InputArgument::REQUIRED, 'RFC Code e.g. scalar_type_hints')
            ->addArgument('Email', InputArgument::REQUIRED, 'Email to notify');;
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

        $rfcCode = $input->getArgument('rfc');

        // Build current RFC
        $currentRfc = $this->rfcService->getRfc($input->getArgument('rfc'));
        $oldRfcPath = sprintf('%s/%s.html', $this->config->get('storagePath'), $rfcCode);

        // Store current RFC if no old RFC exists
        if (!file_exists($oldRfcPath)) {
            file_put_contents($oldRfcPath, $currentRfc->getRawContent());
            return;
        }

        // Get oldRfc
        $oldRfc = $this->rfcService->getRfcFromStorage($rfcCode);

        // Get diffs
        $diffs = $this->diffService->rfcDiff($currentRfc, $oldRfc);

        // TODO: Email diffs, need templates for better rendering

        file_put_contents($oldRfcPath, $currentRfc->getRawContent());
    }
}
