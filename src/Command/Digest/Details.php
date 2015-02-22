<?php


namespace MikeyMike\RfcDigestor\Command\Digest;

use MikeyMike\RfcDigestor\Service\RfcBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Details
 * @author  Michael Woodward <mikeymike.mw@gmail.com>
 */
class Details extends Command
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
        $table = $this->getHelper('table');

        // Build RFC
        $rfc = $this->rfcBuilder
            ->loadFromWiki($rfcCode)
            ->loadDetails()
            ->getRfc();

        $output->writeln("\n<comment>RFC Details</comment>");

        $table->setRows($rfc->getDetails());
        $table->render($output);
    }
}