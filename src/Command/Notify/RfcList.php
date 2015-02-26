<?php


namespace MikeyMike\RfcDigestor\Command\Notify;

use MikeyMike\RfcDigestor\Service\RfcService;
use Noodlehaus\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

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
     * @param RfcService $rfcService
     */
    public function __construct(Config $config, RfcService $rfcService)
    {
        $this->config     = $config;
        $this->rfcService = $rfcService;

        parent::__construct();
    }

    /**
     * Configure Command
     */
    public function configure()
    {
        $this
            ->setName('notify:list')
            ->setDescription('Get notifications of RFC list changes, optionally by sections')
            ->addArgument('Email', InputArgument::REQUIRED, 'Email to notify')
            ->addOption('voting', null, InputOption::VALUE_NONE, 'List RFCs in voting stage')
            ->addOption('discussion', null, InputOption::VALUE_NONE, 'List RFCs under discussion')
            ->addOption('draft', null, InputOption::VALUE_NONE, 'List RFCs in draft stage')
            ->addOption('accepted', null, InputOption::VALUE_NONE, 'List accepted RFCs')
            ->addOption('declined', null, InputOption::VALUE_NONE, 'List declined RFCs')
            ->addOption('withdrawn', null, InputOption::VALUE_NONE, 'List withdrawn RFCs')
            ->addOption('inactive', null, InputOption::VALUE_NONE, 'List inactive RFCs');
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


        // TODO: Diff logic
        // Needs to find if RFC has moved, from and to (without duplication)
        // Needs to find new RFCs

        $previousRfcList = json_decode(file_get_contents($storageFile), true);

        $diffs = [];
        foreach ($currentRfcList as $key => $list) {
            $listDiff = array_diff($list, $previousRfcList[$key]);

            if (!empty($listDiff)) {
                $diffs[] = $listDiff;
            }
        }

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
