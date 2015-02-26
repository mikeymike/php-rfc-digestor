<?php

namespace MikeyMike\RfcDigestor\Command\Rfc;

use MikeyMike\RfcDigestor\Helper\Table;
use MikeyMike\RfcDigestor\Service\RfcService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Summary
 *
 * @package MikeyMike\RfcDigestor
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class Summary extends Command
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
            ->setName('rfc:summary')
            ->setAliases(['rfc:active'])
            ->setDescription('List the vote totals for each active RFC');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $lists      = $this->rfcService->getListsBySections();
        $rfcs       = array_pop($lists);
        $table      = new Table($output);

        $voteStyle = new TableStyle();
        $voteStyle->setCellRowFormat('<comment>%s</comment>');

        $rfcStyle = new TableStyle();
        $rfcStyle->setCellRowFormat('<info>%s</info>');

        foreach ($rfcs as $i => $rfcDetails) {
            $rfcCode = $rfcDetails[1];

            // Build RFC
            $rfc = $this->rfcService->buildRfc($rfcCode);

            $table->addRow([$rfcDetails[0]], $rfcStyle);
            $table->addRow(new TableSeparator());

            foreach ($rfc->getVotes() as $title => $vote) {
                $table->addRow([$title], $voteStyle);

                array_shift($vote['counts']);
                array_shift($vote['headers']);

                foreach ($vote['counts'] as $key => $total) {
                    $table->addRow([$vote['headers'][$key], $total]);
                }
            }

            if ($rfcDetails !== end($rfcs)) {
                $table->addRow(new TableSeparator());
            }
        }

        $table->render($output);
    }
}
