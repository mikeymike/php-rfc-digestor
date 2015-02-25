<?php


namespace MikeyMike\RfcDigestor\Command\Notify;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class Rfc
 *
 * @package MikeyMike\RfcDigestor\Command
 * @author  Michael Woodward <mikeymike.mw@gmail.com>
 */
class Rfc extends Command
{
    /**
     * Configure Command
     */
    public function configure()
    {
        $this
            ->setName('notify:rfc')
            ->setDescription('Get notifications of RFC vote changes')
            ->addArgument('URL', InputArgument::REQUIRED, 'RFC page URL')
            ->addArgument('Email', InputArgument::REQUIRED, 'Email to notify')
            ->addOption('details', null, InputOption::VALUE_NONE, 'Display only RFC details')
            ->addOption('changelog', null, InputOption::VALUE_NONE, 'Display only RFC change log')
            ->addOption('votes', null, InputOption::VALUE_NONE, 'Display only RFC votes');
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
        $url = $input->getArgument('URL');

        // TODO: Verify is URL maybe

        $crawler = new Crawler(null, $url);
    }

}