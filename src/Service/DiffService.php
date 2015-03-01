<?php

namespace MikeyMike\RfcDigestor\Service;

/**
 * Class DiffService
 *
 * @package MikeyMike\RfcDigestor\Service
 * @author  Michael Woodward <mikeymike.mw@gmail.com>
 */
class DiffService
{
    /**
     * @param array $list1
     * @param array $list2
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
