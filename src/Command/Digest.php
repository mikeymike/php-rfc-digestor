<?php


namespace MikeyMike\RfcDigestor\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use MikeyMike\RfcDigestor\Entity\Rfc;

/**
 * Class Digest
 *
 * @package MikeyMike\RfcDigestor
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
            ->setDescription('Quick view of RFC')
            ->addArgument('rfc', InputArgument::REQUIRED, 'RFC Code e.g. scalar_type_hints');
    }

    /**
     * Execute Command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $rfcCode = $input->getArgument('rfc');

        $rfc = new Rfc($rfcCode);

        $output->writeln('<info>RFC Details</info>');

        $table = $this->getHelper('table');
        $table
            ->setRows($rfc->getDetails());
        $table->render($output);


//        // Get URL Arg
//        $url = $input->getArgument('URL');
//
//        $pageContent = file_get_contents($url);
//
//        $crawler = new Crawler($pageContent, $url);
//
//        $tables = $crawler->filter('form[name="doodle__form"] table');
//
//        $tables->each(function(Crawler $table, $i) use ($output) {
//            $title      = $table->filter('.row0 th')->text();
//            $rfcColumns = $table->filter('.row1 > *');
//            $rfcRows    = $table->filter('tr:last-child');
//
//            $cols = [];
//
//            $rfcColumns->each(function(Crawler $col, $i) use (&$cols) {
//                $cols[] = $col->text();
//            });
//
//            $rows = [];
//
//            $rfcRows->each(function(Crawler $rfcRow, $i) use (&$rows) {
//                $row = [];
//
//                $rfcRow->children()->each(function(Crawler $cell, $i) use (&$row) {
//
//                    try {
//                        $row[] = $cell->text();
//                    } catch (InvalidArgumentException $e) {
//
//                    }
//                });
//
//                $rows[] = $row;
//            });
//
//            $output->writeln('');
//            $output->writeln(trim($title));
//
//            $table = $this->getHelper('table');
//            $table
//                ->setHeaders($cols)
//                ->setRows($rows);
//            $table->render($output);
//        });
    }

}