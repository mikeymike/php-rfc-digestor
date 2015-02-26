<?php


namespace MikeyMike\RfcDigestor\Command\Notify;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class Summary
 *
 * @package MikeyMike\RfcDigestor\Command
 * @author  Michael Woodward <mikeymike.mw@gmail.com>
 */
class Summary extends Command
{
    /**
     * Configure Command
     */
    public function configure()
    {
        $this
            ->setName('notify:summary')
            ->setAliases(['notify:active'])
            ->setDescription('Get notifications of RFC vote changes for actively voting RFCs')
            ->addArgument('Email', InputArgument::REQUIRED, 'Email to notify');
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
