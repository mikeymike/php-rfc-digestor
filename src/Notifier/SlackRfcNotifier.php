<?php

namespace MikeyMike\RfcDigestor\Notifier;

use Doctrine\Common\Persistence\ObjectRepository;
use Frlnc\Slack\Core\Commander;
use MikeyMike\RfcDigestor\Entity\Rfc;
use MikeyMike\RfcDigestor\Notifier\RfcNotifierInterface;

/**
 * Class SlackRfcNotifier
 * @package MikeyMike\RfcDigestor\Notifier
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class SlackRfcNotifier implements RfcNotifierInterface
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository
     */
    private $slackSubscriberRepository;

    /**
     * @var \Frlnc\Slack\Core\Commander
     */
    private $commander;

    /**
     * @param ObjectRepository $slackSubscriberRepository
     * @param Commander $commander
     */
    public function __construct(ObjectRepository $slackSubscriberRepository, Commander $commander)
    {
        $this->slackSubscriberRepository = $slackSubscriberRepository;
        $this->commander = $commander;
    }

    /**
     * @param Rfc $rfc
     * @param array $voteDiff
     */
    public function notify(Rfc $rfc, array $voteDiff)
    {
        $attachments = [
            [
                'fallback'      => sprintf('%s updates', $rfc->getName()),
                'title'         => $rfc->getName(),
                'title_link'    => $rfc->getUrl(),
                'color'         => 'good',
            ]
        ];
        foreach ($voteDiff['votes'] as $title => $voteDiffs) {
            $attachment = [
                'text'      => $title,
                'color'     => 'good',
                'fields'    => [],
            ];

            if (!empty($voteDiffs['new'])) {
                $newVotes = array_map(function ($voter, $vote) {
                    return sprintf('%s: %s', $voter, $vote);
                }, array_keys($voteDiffs['new']), $voteDiffs['new']);
                $attachment['fields'][] = [
                    'title' => 'New Votes',
                    'value' => implode(", ", $newVotes),
                ];
            }

            if (!empty($voteDiffs['updated'])) {
                $updatedVotes = array_map(function ($voter, $vote) {
                    return sprintf('%s: %s', $voter, $vote);
                }, array_keys($voteDiffs['updated']), $voteDiffs['updated']);
                $attachment['fields'][] = [
                    'title' => 'Updated Votes',
                    'value' => implode(", ", $updatedVotes),
                ];
            }

            $counts = [];
            foreach ($rfc->getVotes()[$title]['counts'] as $header => $standing) {
                if ($header === 'Real name') {
                    continue;
                }
                $counts[$header] = $standing;
            }

            $counts = array_map(function ($vote, $count) {
                return sprintf('%s: %s', $vote, $count);
            }, array_keys($counts), $counts);

            $attachment['fields'][] = [
                'title' => 'Current Standings',
                'value' => implode(", ", $counts),
            ];

            $attachments[] = $attachment;
        }

        $message = [
            'username'      => 'PHP RFC Digestor',
            'icon_url'      => 'http://php.net/images/logos/php-icon.png',
            'attachments'   => json_encode($attachments),
        ];

        $limit  = 50;
        $offset = 0;
        while ($slackSubscribers = $this->slackSubscriberRepository->findBy([], null, $limit, $offset)) {
            foreach ($slackSubscribers as $slackSubscriber) {
                $this->commander->setToken($slackSubscriber->getToken());
                $message['channel'] = '#' . $slackSubscriber->getChannel();
                $this->commander->execute('chat.postMessage', $message);
            }
            $offset += $limit;
        }
    }
}
