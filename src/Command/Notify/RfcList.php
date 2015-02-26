<?php


namespace MikeyMike\RfcDigestor\Command\Notify;

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
        $output->writeln('<info>Not done this yet :D</info>');

        // TODO: Verify running on cron maybe ?

        // Get URL Arg
//        $url = $input->getArgument('URL');

        // TODO: Verify is URL maybe

//        $crawler = new Crawler(null, $url);
    }
}
