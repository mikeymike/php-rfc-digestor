<?php


namespace MikeyMike\RfcDigestor\Command\Digest;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use MikeyMike\RfcDigestor\Entity\Rfc;

/**
 * Class Votes
 * @author  Michael Woodward <michael@wearejh.com>
 */
class Votes extends Command
{
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
        $rfc     = new Rfc($rfcCode);
        $table   = $this->getHelper('table');

        $output->writeln("\n<comment>RFC Votes</comment>");
        $votes = $rfc->getVotes($input->getOption('detailed'));

        foreach ($votes as $title => $vote) {
            $output->writeln(sprintf("\n<info>%s</info>", $title));
            $table->setHeaders($vote['headers']);

            if ($input->getOption('detailed')) {
                $table->setRows($vote['votes']);
            }

            $table->addRow($vote['counts']);
            $table->render($output);

            // Reset rows
            $table->setRows([]);
        }
    }
}