<?php

namespace MikeyMike\RfcDigestor\Service;

use MikeyMike\RfcDigestor\Entity\Rfc;

/**
 * Class RfcDiffer
 *
 * @package MikeyMike\RfcDigestor\Service
 * @author  Michael Woodward <mikeymike.mw@gmail.com>
 */
class RfcDiffer
{
    /**
     * @var Mailer
     */
    private $mailer;

    /**
     * @var Differ
     */
    private $differ;

    /**
     * @var Rfc
     */
    private $firstRfc;

    /**
     * @var Rfc
     */
    private $secondRfc;

    /**
     * TODO: Create Mailer and Differ classes (check forks of php-diff)
     *
     * @param Mailer $mailer
     * @param Differ $differ
     */
    public function __construct(Mailer $mailer, Differ $differ, Rfc $firstRfc, Rfc $secondRfc)
    {
        $this->mailer    = $mailer;
        $this->differ    = $differ;
        $this->firstRfc  = $firstRfc;
        $this->secondRfc = $secondRfc;
    }

    /**
     * Check if two RFCs have differences
     *
     * @return bool
     */
    public function haveDifferences()
    {
        // TODO: Diff Logic

        return true;
    }

    /**
     * Email diff
     *
     * @param $to array|string
     * @param $from string
     * @return bool
     */
    public function sendDifferences($to, $from)
    {
        // TODO: Find a good mailer
        return true;
    }

    /**
     * Check have diff and send
     *
     * @param $to
     * @param $from
     * @return bool
     */
    public function compareAndSend($to, $from)
    {
        if (!$this->haveDifferences()) {
            return false;
        }

        return $this->sendDifferences($to, $from);
    }
}
