<?php


namespace MikeyMike\RfcDigestor\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use MikeyMike\RfcDigestor\Entity\Rfc;

/**
 * Class RfcList
 *
 * @package MikeyMike\RfcDigestor
 * @author  Michael Woodward <michael@wearejh.com>
 */
class RfcList extends Command
{
    public $lists = [];

    /**
     * Configure Command
     */
    public function configure()
    {
        $this
            ->setName('list')
            ->setDescription('List RFC, split by sections')
            ->addOption('voting', null, InputOption::VALUE_NONE, 'List RFCs in voting stage')
            ->addOption('discussion', null, InputOption::VALUE_NONE, 'List RFCs under discussion')
            ->addOption('draft', null, InputOption::VALUE_NONE, 'List RFCs in draft stage')
            ->addOption('accepted', null, InputOption::VALUE_NONE, 'List accepted RFCs')
            ->addOption('declined', null, InputOption::VALUE_NONE, 'List declined RFCs')
            ->addOption('withdrawn', null, InputOption::VALUE_NONE, 'List withdrawn RFCs')
            ->addOption('inactive', null, InputOption::VALUE_NONE, 'List inactive RFCs');
//            ->addOption('implemented', null, InputOption::VALUE_NONE, 'List implemented RFCs') // Maybe not this
    }

    /**
     * Execute Command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (count($input->getOptions()) === 0 || $this->getOption('voting')) {
            $this->getInVoting();
        }

        if ($this->getOption('discussion')) {
            $this->getInDiscussion();
        }

        if ($this->getOption('draft')) {
            $this->getInDraft();
        }

        if ($this->getOption('accepted')) {
            $this->getAccepted();
        }

        if ($this->getOption('declined')) {
            $this->getDeclined();
        }

        if ($this->getOption('withdrawn')) {
            $this->getWithdrawn();
        }

        if ($this->getOption('inactive')) {
            $this->getInactive();
        }

        $table = $this->getHelper('table');
        foreach ($this->lists as $title => $list) {
            $output->writeln(sprintf("\n<comment></comment>", $title));

            $table->setRows($list);
            $table->render($output);
        }
    }

    public function getInVoting()
    {

    }

    public function getInDiscussion()
    {

    }

    public function getInDraft()
    {

    }

    public function getAccepted()
    {

    }

    public function getDeclined()
    {

    }

    public function getWithdrawn()
    {

    }

    public function getInactive()
    {

    }

    public function getList()
    {

    }
}