<?php


namespace MikeyMike\RfcDigestor\Command\Digest;

use MikeyMike\RfcDigestor\Service\RfcBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Votes
 * @author  Michael Woodward <mikeymike.mw@gmail.com>
 */
class Votes extends Command
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
            ->setName('digest:votes')
            ->setDescription('Quick view of current votes')
            ->addArgument('rfc', InputArgument::REQUIRED, 'RFC Code e.g. scalar_type_hints')
            ->addOption('detailed', 'd', InputOption::VALUE_NONE, 'Display each vote');
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

        // Build RFC
        $rfc = $this->rfcBuilder
            ->loadFromWiki($rfcCode)
            ->loadVotes()
            ->getRfc();

        $output->writeln("\n<comment>RFC Votes</comment>");

        if ($rfc->getVoteDescription()) {
            $output->writeln(sprintf("\n%s", $rfc->getVoteDescription()));
        }

        foreach ($rfc->getVotes() as $title => $vote) {
            $output->writeln(sprintf("\n<info>%s</info>", $title));

            $table->setHeaders($vote['headers']);
            $table->setRows($input->getOption('detailed') ? $vote['votes'] : []);
            $table->addRow($vote['counts']);
            $table->render($output);
        }
    }
}