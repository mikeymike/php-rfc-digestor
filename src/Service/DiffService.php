<?php

namespace MikeyMike\RfcDigestor\Service;

use MikeyMike\RfcDigestor\Entity\Rfc;

/**
 * Class DiffService
 *
 * @package MikeyMike\RfcDigestor\Service
 * @author  Michael Woodward <mikeymike.mw@gmail.com>
 */
class DiffService
{
    /**
     * Returns array of changes in Rfc 2
     *
     * @param Rfc $rfc1
     * @param Rfc $rfc2
     * @return array
     */
    public function rfcDiff(Rfc $rfc1, Rfc $rfc2)
    {

        return [];
    }

    /**
     * Get a parsed diff of two RFC lists
     *
     * @param array $list1
     * @param array $list2
     * @return array
     */
    public function listDiff($list1, $list2)
    {
        $diffs       = [];
        $parsedDiffs = [];

        foreach ($list1 as $section => $list) {
            $diffs[$section] = array_diff(array_keys($list), array_keys($list2[$section]));
        }

        foreach ($diffs as $section => $diff) {
            foreach ($diff as $title) {
                $from = $this->findFromKey($list2, $title);

                $parsedDiffs[$title] = [
                    'to'   => $section,
                    'from' => $from
                ];
            }
        }

        return $parsedDiffs;
    }

    /**
     * Gets the section name for the RFC
     *
     * @param array  $list
     * @param string $title
     * @return string
     */
    private function findFromKey($list, $title)
    {
        foreach ($list as $key => $section) {
            if (array_key_exists($title, $section)) {
                return $key;
            }
        }
    }
}
