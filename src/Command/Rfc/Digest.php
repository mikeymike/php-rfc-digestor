<?php


namespace MikeyMike\RfcDigestor\Command\Rfc;

use MikeyMike\RfcDigestor\Entity\Rfc;
use MikeyMike\RfcDigestor\Service\RfcService;
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
     * @var RfcService
     */
    protected $rfcService;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @param RfcService $rfcService
     */
    public function __construct(RfcService $rfcService)
    {
        $this->rfcService   = $rfcService;
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

        // Get only true options
        $setArguments = array_filter($input->getOptions());

        // Set default if no options passed
        if (count($setArguments) === 0 || (count($setArguments) === 1 && $input->getOption('detailed'))) {
            $input->setOption('details', true);
            $input->setOption('changelog', true);
            $input->setOption('votes', true);
        }

        // Build RFC
        $rfc = $this->rfcService->buildRfc(
            $rfcCode,
            $input->getOption('details'),
            $input->getOption('changelog'),
            $input->getOption('votes')
        );

        if ($input->getOption('details')) {
            $this->showDetails($rfc);
        }

        if ($input->getOption('changelog')) {
            $this->showChangeLog($rfc);
        }

        if ($input->getOption('votes')) {
            $this->showVotes($rfc);
        }
    }

    /**
     * @param Rfc $rfc
     */
    private function showDetails(Rfc $rfc)
    {
        $table = $this->getHelper('table');

        $this->output->writeln("\n<comment>RFC Details</comment>");

        $table->setRows($rfc->getDetails());
        $table->render($this->output);
    }

    /**
     * @param Rfc $rfc
     */
    private function showVotes(Rfc $rfc)
    {
        $table = $this->getHelper('table');

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
     * @param Rfc $rfc
     */
    private function showChangeLog(Rfc $rfc)
    {
        $table = $this->getHelper('table');

        $this->output->writeln("\n<comment>RFC ChangeLog</comment>");

        if (!$rfc->getChangeLog()) {
            $this->output->writeln('<info>No change log provided</info>');
            return;
        }

        $table->setRows($rfc->getChangeLog());
        $table->render($this->output);
    }
}
