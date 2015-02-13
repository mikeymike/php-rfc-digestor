<?php


namespace MikeyMike\RfcDigestor\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class Digest
 *
 * @package MikeyMike\RfcDigestor\Command
 * @author  Michael Woodward <michael@wearejh.com>
 */
class Digest extends Command
{
    /**
     * Configure Command
     */
    public function configure()
    {
        $this
            ->setName('digest')
            ->setDescription('Quick overview of an RFC page')
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
        // Get URL Arg
        $url = $input->getArgument('URL');

        $pageContent = file_get_contents($url);

        $crawler = new Crawler($pageContent, $url);

        $tables = $crawler->filter('form[name="doodle__form"] table');

        $tables->each(function(Crawler $table, $i) use ($output) {
            $title      = $table->filter('.row0 th')->text();
            $rfcColumns = $table->filter('.row1 > *');
            $rfcRows    = $table->filter('tr:not(.row0):not(.row1)');

            $cols = [];

            $rfcColumns->each(function(Crawler $col, $i) use (&$cols) {
                $cols[] = $col->text();
            });

            $rows = [];

            $rfcRows->each(function(Crawler $rfcRow, $i) use (&$rows) {
                $row = [];
                $rfcRow->filter('td')->each(function(Crawler $cell, $i) use (&$row) {
                    $row[] = $cell->text();
                });

                $rows[] = $row;
            });

            $output->writeln($title);

            $table = $this->getHelper('table');
            $table
                ->setHeaders($cols)
                ->setRows($rows);
            $table->render($output);
        });
    }

}