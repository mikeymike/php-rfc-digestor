<?php


namespace MikeyMike\RfcDigestor\Command\Rfc;

use MikeyMike\RfcDigestor\Helper\Table;
use MikeyMike\RfcDigestor\Service\RfcService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RfcList
 *
 * @package MikeyMike\RfcDigestor
 * @author  Michael Woodward <mikeymike.mw@gmail.com>
 */
class RfcList extends Command
{
    /**
     * @var RfcService
     */
    protected $rfcService;

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
            ->setName('rfc:list')
            ->setDescription('List RFC, split by sections')
            ->addOption('voting', null, InputOption::VALUE_NONE, 'List RFCs in voting stage')
            ->addOption('discussion', null, InputOption::VALUE_NONE, 'List RFCs under discussion')
            ->addOption('draft', null, InputOption::VALUE_NONE, 'List RFCs in draft stage')
            ->addOption('accepted', null, InputOption::VALUE_NONE, 'List accepted RFCs')
            ->addOption('declined', null, InputOption::VALUE_NONE, 'List declined RFCs')
            ->addOption('withdrawn', null, InputOption::VALUE_NONE, 'List withdrawn RFCs')
            ->addOption('inactive', null, InputOption::VALUE_NONE, 'List inactive RFCs')
            ->addOption('all', null, InputOption::VALUE_NONE, 'List all options');
    }

    /**
     * Execute Command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $sections = [];

        if (!$input->getOption('all')) {
            if (count(array_filter($input->getOptions())) === 0 || $input->getOption('voting')) {
                $sections[] = RfcService::IN_VOTING;
            }

            if ($input->getOption('discussion')) {
                $sections[] = RfcService::DISCUSSION;
            }

            if ($input->getOption('draft')) {
                $sections[] = RfcService::DRAFT;
            }

            if ($input->getOption('accepted')) {
                $sections[] = RfcService::ACCEPTED;
            }

            if ($input->getOption('declined')) {
                $sections[] = RfcService::DECLINED;
            }

            if ($input->getOption('withdrawn')) {
                $sections[] = RfcService::WITHDRAWN;
            }

            if ($input->getOption('inactive')) {
                $sections[] = RfcService::INACTIVE;
            }
        }

        $table      = new Table($output);
        $titleStyle = new TableStyle();
        $titleStyle->setCellRowFormat('<comment>%s</comment>');

        $lists = $this->rfcService->getLists($sections);

        $table->setHeaders(['RFC', 'RFC Code']);

        foreach ($lists as $heading => $list) {
            $table->addRow([$heading], $titleStyle);
            $table->addRow(new TableSeparator());

            foreach ($list as $listing) {
                $table->addRow($listing);
            }

            if ($list !== end($lists)) {
                $table->addRow(new TableSeparator());
            }
        }

        $table->render();
    }
}
