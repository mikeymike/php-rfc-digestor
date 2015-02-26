<?php


namespace MikeyMike\RfcDigestor\Command\Rfc;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class RfcList
 *
 * TODO: Strip out into service
 * @package MikeyMike\RfcDigestor
 * @author  Michael Woodward <mikeymike.mw@gmail.com>
 */
class RfcList extends Command
{
    public $lists = [];

    /**
     * @var Crawler
     */
    public $crawler;

    /**
     * Configure Command
     */
    public function configure()
    {
        $this
            ->setName('rfc:list')
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
        $this->crawler = new Crawler(file_get_contents('https://wiki.php.net/rfc'));

        if (count(array_filter($input->getOptions())) === 0 || $input->getOption('voting')) {
            $this->getInVoting();
        }

        if ($input->getOption('discussion')) {
            $this->getInDiscussion();
        }

        if ($input->getOption('draft')) {
            $this->getInDraft();
        }

        if ($input->getOption('accepted')) {
            $this->getAccepted();
        }

        if ($input->getOption('declined')) {
            $this->getDeclined();
        }

        if ($input->getOption('withdrawn')) {
            $this->getWithdrawn();
        }

        if ($input->getOption('inactive')) {
            $this->getInactive();
        }

        $table = $this->getHelper('table');
        foreach ($this->lists as $title => $list) {
            $output->writeln(sprintf("\n<comment>%s</comment>", $title));

            $table->setHeaders([
                'RFC', 'RFC Code'
            ]);
            $table->setRows($list);
            $table->render($output);
        }
    }

    public function getInVoting()
    {
        $this->getList('in_voting_phase');
    }

    public function getInDiscussion()
    {
        $this->getList('under_discussion');
    }

    public function getInDraft()
    {
        $this->getList('in_draft');
    }

    public function getAccepted()
    {
        $this->getList('accepted');
    }

    public function getDeclined()
    {
        $this->getList('declined');
    }

    public function getWithdrawn()
    {
        $this->getList('withdrawn');
    }

    public function getInactive()
    {
        $this->getList('inactive');
    }

    /**
     * @param string $headingId
     */
    public function getList($headingId)
    {
        # List key is the heading
        $listKey = $this->crawler->filter(sprintf('#%s', $headingId))->text();

        $this->lists[$listKey] = [];

        $this->crawler->filter(sprintf('#%s + .level2 .li', $headingId))->each(function ($rfc, $i) use ($listKey) {

            $link = $rfc->filter('a');

            $row = [
                $link->text(),
                basename($link->attr('href'))
            ];

            $this->lists[$listKey][] = $row;
        });
    }
}
