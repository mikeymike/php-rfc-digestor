<?php


namespace MikeyMike\RfcDigestor\Command\Digest;

use MikeyMike\RfcDigestor\Service\RfcBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use MikeyMike\RfcDigestor\Entity\Rfc;

/**
 * Class ChangeLog
 * @author  Michael Woodward <mikeymike.mw@gmail.com>
 */
class ChangeLog extends Command
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
            ->setName('digest:changelog')
            ->setDescription('Quick view of RFC change log')
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
        $table   = $this->getHelper('table');

        // Build RFC
        $rfc = $this->rfcBuilder
            ->loadFromWiki($rfcCode)
            ->loadChangeLog()
            ->getRfc();

        if (!$rfc->getChangeLog()) {
            $output->writeln('<error>No change log found!</error>');
            return;
        }

        $output->writeln("\n<comment>RFC Change Log</comment>");

        $table->setRows($rfc->getChangeLog());
        $table->render($output);
    }
}