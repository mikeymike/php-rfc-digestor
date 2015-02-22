<?php


namespace MikeyMike\RfcDigestor\Command;

use MikeyMike\RfcDigestor\Service\RfcBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Digest
 *
 * @package MikeyMike\RfcDigestor
 * @author  Michael Woodward <mikeymike.mw@gmail.com>
 */
class Digest extends Command
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var RfcBuilder
     */
    protected $rfcBuilder;

    /**
     * @param array      $config
     * @param RfcBuilder $rfcBuilder
     */
    public function __construct($config = [], RfcBuilder $rfcBuilder)
    {
        $this->config       = $config;
        $this->rfcBuilder   = $rfcBuilder;

        parent::__construct();
    }

    /**
     * Configure Command
     */
    public function configure()
    {
        $this
            ->setName('digest')
            ->setDescription('Quick view of RFC')
            ->addArgument('rfc', InputArgument::REQUIRED, 'RFC Code e.g. scalar_type_hints')
            ->addOption('detailed', 'd', InputOption::VALUE_NONE, 'Show detailed table of votes');
            // TODO: Interactive Fuzzy search
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
        $table   = $this->getHelper('table');
        $rfc     = $this->rfcBuilder->loadFromWiki($rfcCode, true)->getRfc();

        $output->writeln("\n<comment>RFC Details</comment>");

        $table->setRows($rfc->getDetails());
        $table->render($output);

        // Might not contain changelog
        if ($rfc->getChangeLog()) {
            $output->writeln("\n<comment>RFC Change Log</comment>");
            $table->setHeaders([]);
            $table->setRows($rfc->getChangeLog());
            $table->render($output);
        }

        if (count($rfc->getVotes()) > 0) {
            $output->writeln("\n<comment>RFC Votes</comment>");
            $output->writeln(sprintf("\n%s", $rfc->getVoteDescription()));

            foreach ($rfc->getVotes() as $title => $vote) {
                $output->writeln(sprintf("\n<info>%s</info>", $title));

                $table->setHeaders($vote['headers']);
                $table->setRows($input->getOption('detailed') ? $vote['votes'] : []);
                $table->addRow($vote['counts']);
                $table->render($output);
            }
        } else {
            $output->writeln("\n<comment>No votes yet</comment>");
        }
    }
}