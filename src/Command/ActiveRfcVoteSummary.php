<?php

namespace MikeyMike\RfcDigestor\Command;

use MikeyMike\RfcDigestor\Helper\Table;
use MikeyMike\RfcDigestor\Service\RfcBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\CssSelector\CssSelector;

/**
 * Class RfcList
 *
 * @package MikeyMike\RfcDigestor
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class ActiveRfcVoteSummary extends Command
{

    /**
     * @var RfcBuilder
     */
    protected $rfcBuilder;

    /**
     * @param RfcBuilder $rfcBuilder
     */
    public function __construct(RfcBuilder $rfcBuilder)
    {
        $this->rfcBuilder = $rfcBuilder;
        parent::__construct();
    }

    /**
     * Configure Command
     */
    public function configure()
    {
        $this
            ->setName('rfc:active')
            ->setAliases(['rfc:summary'])
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
        $rfcs       = $this->getInVotingList();
        $totalRfcs  = count($rfcs);
        $table      = new Table($output);

        $voteStyle = new TableStyle();
        $voteStyle->setCellRowFormat('<comment>%s</comment>');

        $rfcStyle = new TableStyle();
        $rfcStyle->setCellRowFormat('<info>%s</info>');

        foreach ($rfcs as $i => $rfcDetails) {
            $rfcCode = $rfcDetails[1];

            // Build RFC
            $rfc = $this->rfcBuilder
                ->loadFromWiki($rfcCode)
                ->loadVotes()
                ->getRfc();

            $table->addRow(array($rfcDetails[0]), $rfcStyle);
            $table->addRow(new TableSeparator());

            foreach ($rfc->getVotes() as $title => $vote) {
                $table->addRow(array($title), $voteStyle);

                array_shift($vote['counts']);
                foreach ($vote['counts'] as $key => $total) {
                    $table->addRow(array($vote['headers'][$key], $total));
                }
            }

            if ($i < ($totalRfcs - 1)) {
                $table->addRow(new TableSeparator());
            }
        }
        $table->render($output);
    }

    /**
     * @return array
     */
    private function getInVotingList()
    {
        $listKeyXPath   = CssSelector::toXPath('#in_voting_phase');
        $activeXPath    = CssSelector::toXPath('#in_voting_phase + div.level2 div.li a');

        libxml_use_internal_errors(true);
        $document = new \DOMDocument();
        $document->loadHTMLFile('https://wiki.php.net/rfc');
        libxml_use_internal_errors(false);

        $xPath          = new \DOMXPath($document);
        $activeTitle    = $xPath->query($listKeyXPath)->item(0)->textContent;

        $rfcs = [];
        foreach ($xPath->query($activeXPath) as $item) {
            $rfcs[] = [
                $item->textContent,
                basename($item->getAttribute('href'))
            ];
        }
        return $rfcs;
    }
}