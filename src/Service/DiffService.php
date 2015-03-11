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
        return [
            'details'   => $this->recursiveArrayDiff($rfc1->getDetails(), $rfc2->getDetails()),
            'changeLog' => $this->recursiveArrayDiff($rfc1->getChangeLog(), $rfc2->getChangeLog()),
            'votes'     => $this->recursiveArrayDiff($rfc1->getVotes(), $rfc2->getVotes())
        ];
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

    /**
     * Get diff of arrays recursively
     * 
     * @param array $arr1
     * @param array $arr2
     * @return array
     */
    private function recursiveArrayDiff($arr1, $arr2)
    {
        $diff = [];
        foreach ($arr1 as $key => $value) {

            if (!is_array($value)) {
                if (!isset($arr2[$key]) || $arr2[$key] != $value) {
                    $diff[$key] = $value;
                }
                continue;
            }

            if (!isset($arr2[$key])) {
                $diff[$key] = $value;
                continue;
            }

            if (!is_array($arr2[$key])) {
                $difference[$key] = $value;
                continue;
            }

            $new_diff = $this->recursiveArrayDiff($value, $arr2[$key]);
            if ($new_diff != false) {
                $diff[$key] = $new_diff;
            }
        }

        return $diff;
    }
}
