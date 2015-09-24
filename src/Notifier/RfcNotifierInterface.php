<?php


namespace MikeyMike\RfcDigestor\Notifier;

use MikeyMike\RfcDigestor\Entity\Rfc;

/**
 * Interface RfcNotifierInterface
 * @package MikeyMike\RfcDigestor\Notifier
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
interface RfcNotifierInterface
{
    /**
     * @param Rfc $rfc
     * @param array $voteDiff
     */
    public function notify(Rfc $rfc, array $voteDiff);
}
