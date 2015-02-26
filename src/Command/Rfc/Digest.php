<?php


namespace MikeyMike\RfcDigestor\Command\Rfc;

use MikeyMike\RfcDigestor\Service\RfcBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Digest
 * @author  Michael Woodward <mikeymike.mw@gmail.com>
 */
class Digest extends Command
{
    /**
     * @var RfcBuilder
     */
    protected $rfcBuilder;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @param RfcBuilder $rfcBuilder
     */
    public function __construct(RfcBuilder $rfcBuilder)
    {
        $this->rfcBuilder   = $rfcBuilder;

        parent::__construct();
    }

    /**
     * Configure Command
     */
    public function configure()
    {
        $this
            ->setName('rfc:digest')
            ->setDescription('Digest an RFCs contents')
            ->addArgument('rfc', InputArgument::REQUIRED, 'RFC Code e.g. scalar_type_hints')
            ->addOption('details', null, InputOption::VALUE_NONE, 'Display only RFC details')
            ->addOption('changelog', null, InputOption::VALUE_NONE, 'Display only RFC change log')
            ->addOption('votes', null, InputOption::VALUE_NONE, 'Display only RFC votes')
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
        $this->input    = $input;
        $this->output   = $output;
        $rfcCode        = $input->getArgument('rfc');

        // Build RFC
        $this->rfcBuilder->loadFromWiki($rfcCode);

        // Get only true options
        $setArguments = array_filter($input->getOptions());

        // Set default if no options passed
        if (count($setArguments) === 0 || (count($setArguments) === 1 && $input->getOption('detailed'))) {
            $input->setOption('details', true);
            $input->setOption('changelog', true);
            $input->setOption('votes', true);
        }

        if ($input->getOption('details')) {
            $this->showDetails();
        }

        if ($input->getOption('changelog')) {
            $this->showChangeLog();
        }

        if ($input->getOption('votes')) {
            $this->showVotes();
        }
    }

    /**
     * Output the RFC details
     */
    private function showDetails()
    {
        $table = $this->getHelper('table');

        // Build RFC
        $rfc = $this->rfcBuilder
            ->loadDetails()
            ->getRfc();

        $this->output->writeln("\n<comment>RFC Details</comment>");

        $table->setRows($rfc->getDetails());
        $table->render($this->output);
    }

    /**
     * Output a table of RFC votes
     */
    private function showVotes()
    {
        $table = $this->getHelper('table');

        // Build RFC
        $rfc = $this->rfcBuilder
            ->loadVotes()
            ->getRfc();

        $this->output->writeln("\n<comment>RFC Votes</comment>");

        if ($rfc->getVoteDescription()) {
            $this->output->writeln(sprintf("\n%s", $rfc->getVoteDescription()));
        }

        if (count($rfc->getVotes()) === 0) {
            $this->output->writeln('<info>Rfc has no votes</info>');
        }

        foreach ($rfc->getVotes() as $title => $vote) {
            $this->output->writeln(sprintf("\n<info>%s</info>", $title));

            $table->setHeaders($vote['headers']);
            $table->setRows($this->input->getOption('detailed') ? $vote['votes'] : []);
            $table->addRow($vote['counts']);
            $table->render($this->output);
        }
    }

    /**
     * Output the RFC change log
     */
    private function showChangeLog()
    {
        $table = $this->getHelper('table');

        // Build RFC
        $rfc = $this->rfcBuilder
            ->loadChangeLog()
            ->getRfc();

        if (!$rfc->getChangeLog()) {
            $this->output->writeln('<info>No change log provided</info>');
            return;
        }

        $this->output->writeln("\n<comment>RFC ChangeLog</comment>");

        $table->setRows($rfc->getChangeLog());
        $table->render($this->output);
    }
}
