<?php


namespace MikeyMike\RfcDigestor\Command\Notify;

use Noodlehaus\Config;
use MikeyMike\RfcDigestor\Service\RfcService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Summary
 *
 * @package MikeyMike\RfcDigestor\Command
 * @author  Michael Woodward <mikeymike.mw@gmail.com>
 */
class Voting extends Command
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
     * @param Config $config
     * @param RfcService $rfcService
     */
    public function __construct(Config $config, RfcService $rfcService)
    {
        $this->config       = $config;
        $this->rfcService   = $rfcService;

        parent::__construct();
    }

    /**
     * Configure Command
     */
    public function configure()
    {
        $this
            ->setName('notify:voting')
            ->setDescription('Get notifications of RFC vote changes for actively voting RFCs');
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

        // Get a list of RFCs using RFCService
        $list = $this->rfcService->getLists(array(RfcService::IN_VOTING));

        // Call notify:rfc for each one in voting list
        $command = $this->getApplication()->find('notify:rfc');
        $args    = ['command' => 'notify:rfc'];

        foreach (reset($list) as $name => $listing) {
            $args['rfc'] =  array_pop($listing);
            $inputArgs   = new ArrayInput($args);

            $command->run($inputArgs, $output);
        }
    }
}
