<?php

namespace MikeyMike\RfcDigestor;

use MikeyMike\RfcDigestor\Notifier\RfcNotifierInterface;
use Noodlehaus\Config;
use MikeyMike\RfcDigestor\Service\RfcService;
use MikeyMike\RfcDigestor\Service\DiffService;

/**
 * Class Notifier
 * @package MikeyMike\RfcDigestor
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */

class RfcNotifier
{

    /**
     * @var Config
     */
    private $config;

    /**
     * @var RfcService
     */
    private $rfcService;

    /**
     * @var DiffService
     */
    private $diffService;

    /**
     * @var array
     */
    private $notifiers;

    /**
     * @param Config $config
     * @param RfcService $rfcService
     * @param DiffService $diffService
     */
    public function __construct(Config $config, RfcService $rfcService, DiffService $diffService, array $notifiers)
    {
        $this->config = $config;
        $this->rfcService = $rfcService;
        $this->diffService = $diffService;
        $this->notifiers = array_map(function (RfcNotifierInterface $notifier) {
            return $notifier;
        }, $notifiers);
    }

    /**
     * @param string $rfcCode
     */
    public function notify($rfcCode)
    {
        $oldRfcPath = sprintf('%s/%s.html', $this->config->get('storagePath'), $rfcCode);

        try {
            // Build current RFC
            $currentRfc = $this->rfcService->getRfc($rfcCode);
        } catch (\InvalidArgumentException $e) {
            throw new \RuntimeException('Invalid RFC code, check rfc:list for valid codes');
        }

        if (!file_exists($oldRfcPath)) {
            file_put_contents($oldRfcPath, $currentRfc->getRawContent());
            $oldRfc = new \MikeyMike\RfcDigestor\Entity\Rfc();
        } else {
            try {
                // Get oldRfc
                $oldRfc = $this->rfcService->getRfcFromStorage($rfcCode);
            } catch (\InvalidArgumentException $e) {
                throw new \RuntimeException($e->getMessage());
            }
        }

        // Get diffs
        $diffs = $this->diffService->rfcDiff($currentRfc, $oldRfc);

        // Only send email if we have diffs
        if (count(array_filter($diffs)) === 0) {
            return;
        }

        foreach ($this->notifiers as $notifier) {
            $notifier->notify($currentRfc, $diffs);
        }

        file_put_contents($oldRfcPath, $currentRfc->getRawContent());
    }
}
