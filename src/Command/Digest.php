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
        $rfc     = new Rfc($rfcCode);
        $table   = $this->getHelper('table');

        $output->writeln("\n<comment>RFC Details</comment>");

        $table
            ->setRows($rfc->getDetails());
        $table->render($output);


        // TODO: Option to disp users or not

        $output->writeln("\n<comment>RFC Votes</comment>");
        $votes = $rfc->getVotes();

        foreach ($votes as $title => $vote) {
            $output->writeln(sprintf("\n<info>%s</info>", $title));
            $table
                ->setHeaders($vote['headers'])
                ->setRows($vote['votes'])
                ->addRow($vote['counts']);
            $table->render($output);
        }

        // Might not contain changelog
        if ($rfc->getChangeLog()) {
            $output->writeln("\n<comment>RFC Change Log</comment>");

            $table
                ->setRows($rfc->getChangeLog());
            $table->render($output);
        }
    }
}