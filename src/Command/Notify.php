<?php


namespace MikeyMike\RfcDigestor\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class Notify
 *
 * @package MikeyMike\RfcDigestor\Command
 * @author  Michael Woodward <mikeymike.mw@gmail.com>
 */
class Notify extends Command
{
    /**
     * Configure Command
     */
    public function configure()
    {
        $this
            ->setName('notify')
            ->setDescription('Get notifications of RFC vote changes')
            ->addArgument('URL', InputArgument::REQUIRED, 'RFC page URL');
    }

    /**
     * Execute Command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        // TODO: Verify running on cron maybe ?

        // Get URL Arg
        $url = $input->getArgument('URL');

        // TODO: Verify is URL maybe

        $crawler = new Crawler(null, $url);
    }

}