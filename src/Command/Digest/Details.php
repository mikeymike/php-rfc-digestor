<?php


namespace MikeyMike\RfcDigestor\Command\Digest;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use MikeyMike\RfcDigestor\Entity\Rfc;

/**
 * Class Details
 * @author  Michael Woodward <mikeymike.mw@gmail.com>
 */
class Details extends Command
{
    /**
     * Configure Command
     */
    public function configure()
    {
        $this
            ->setName('digest:details')
            ->setDescription('Quick view of RFC details')
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

        $table->setRows($rfc->getDetails());
        $table->render($output);
    }
}