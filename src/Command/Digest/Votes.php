<?php


namespace MikeyMike\RfcDigestor\Command\Digest;

use SebastianBergmann\RecursionContext\InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
            ->addArgument('rfc', InputArgument::REQUIRED, 'RFC Code e.g. scalar_type_hints');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $rfcCode = $input->getArgument('rfc');

        $rfc = new Rfc($rfcCode);
    }
}