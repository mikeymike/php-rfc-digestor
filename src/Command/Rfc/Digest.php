<?php


namespace MikeyMike\RfcDigestor\Command\Rfc;

use MikeyMike\RfcDigestor\Entity\Rfc;
use MikeyMike\RfcDigestor\Service\RfcService;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
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
        $this->rfcService = $rfcService;
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
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input    = $input;
        $this->output   = $output;
        $rfcCode        = $input->getArgument('rfc');
        $args           = array_fill_keys(['details', 'changelog', 'votes'], true);

        // Merge relevant values
        array_walk($args, function (&$option, $key) use ($input) {
            $option = $input->getOption($key);
        });

        // Set default if no options passed
        if (count(array_filter($args)) === 0) {
            $args = [
                'details'   => true,
                'changelog' => true,
                'votes'     => true
            ];
        }

        try {
            // Build RFC
            $rfc = $this->rfcService->getRfc(
                $rfcCode,
                $args['details'],
                $args['changelog'],
                $args['votes']
            );
        } catch (\InvalidArgumentException $e) {
            $output->writeln('<error>Invalid RFC code, check rfc:list for valid codes</error>');
            return;
        }

        if ($args['details']) {
            $this->showDetails($rfc);
        }

        if ($args['changelog']) {
            $this->showChangeLog($rfc);
        }

        if ($args['votes']) {
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
        $this->output->writeln(sprintf("\n<info>%s</info>", $rfc->getName()));

        $table->setRows($this->rfcService->getDetailsAsTableRows($rfc));
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

            $votes = $this->rfcService->getVotesAsTableRows($rfc, $title);

            // Lets have fancy tick marks
            array_walk_recursive($votes, function (&$vote) {
                if ($vote === true) {
                    $vote = "\xE2\x9C\x93";
                }
            });

            $table->setHeaders($vote['headers']);
            $table->setRows($this->input->getOption('detailed') ? $votes : []);
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

        $table->setRows($this->rfcService->getChangeLogsAsTableRows($rfc));
        $table->render($this->output);
    }
}
