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
     * Returns array of changes in RFC 1 compared to RFC 2
     *
     * @param Rfc $rfc1
     * @param Rfc $rfc2
     * @return array
     */
    public function rfcDiff(Rfc $rfc1, Rfc $rfc2)
    {
        // Get any vote diffs between two RFCs
        $voteDiffs = $this->recursiveArrayDiff($rfc1->getVotes(), $rfc2->getVotes());

        return [
            'details'   => $this->recursiveArrayDiff($rfc1->getDetails(), $rfc2->getDetails()),
            'changeLog' => $this->recursiveArrayDiff($rfc1->getChangeLog(), $rfc2->getChangeLog()),
            'votes'     => $this->parseVotesDiff($voteDiffs, $rfc2->getVotes())
        ];
    }

    /**
     * Parse a vote diff into new and updated votes
     *
     * @param array $voteDiffs
     * @param array $comparisonVotes
     * @return array
     */
    protected function parseVotesDiff(array $voteDiffs, array $comparisonVotes)
    {
        // Split them into new and updated
        $splitVotesDiffs = [];
        foreach ($voteDiffs as $key => $votes) {
            $rfc2Votes    = $comparisonVotes;
            $newVotes     = array_diff_key($votes['votes'], $rfc2Votes[$key]['votes']);
            $updatedVotes = array_intersect_key($votes['votes'], $rfc2Votes[$key]['votes']);

            /**
             * Quick function to get vote
             *
             * @param $votes
             * @return array
             */
            $getVote = function ($votes) {
                $parsedVotes = [];
                foreach ($votes as $voter => $vote) {
                    // Get true vote and it's key
                    $vote = array_filter($vote);
                    $vote = array_keys($vote);

                    // Set the voters parsed vote
                    $parsedVotes[$voter] = reset($vote);
                }

                return $parsedVotes;
            };

            $newVotes     = $getVote($newVotes);
            $updatedVotes = $getVote($updatedVotes);

            $splitVotesDiffs[$key]['new']     = $newVotes;
            $splitVotesDiffs[$key]['updated'] = $updatedVotes;
        }

        return $splitVotesDiffs;
    }

    /**
     * Get a parsed diff of two RFC lists
     *
     * @param array $list1
     * @param array $list2
     * @return array
     */
    public function listDiff(array $list1, array $list2)
    {
        $diffs       = [];
        $parsedDiffs = [];

        foreach ($list1 as $section => $list) {
            $diffs[$section] = array_diff(array_keys($list), array_keys($list2[$section]));
        }

        foreach ($diffs as $section => $diff) {
            foreach ($diff as $title) {
                $from = $this->findRfcListKey($list2, $title);

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
    protected function findRfcListKey(array $list, $title)
    {
        $from = '';
        foreach ($list as $key => $section) {
            if (array_key_exists($title, $section)) {
                $from = $key;
                break;
            }
        }

        return $from;
    }

    /**
     * Get diff of assoc arrays recursively
     *
     * @param array $arr1
     * @param array $arr2
     * @return array
     */
    protected function recursiveArrayDiff(array $arr1, array $arr2)
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
                $diff[$key] = $value;
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
