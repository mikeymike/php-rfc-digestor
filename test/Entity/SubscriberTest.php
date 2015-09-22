<?php


namespace MikeyMike\RfcDigestor\Entity;

use PHPUnit_Framework_TestCase;

/**
 * Class SubscriberTest
 * @package MikeyMike\RfcDigestor\Entity
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class SubscriberTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Subscriber
     */
    protected $subscriber;

    public function setUp()
    {
        $this->subscriber = new Subscriber;
    }

    public function testGettersAndSetters()
    {
        $this->assertNull($this->subscriber->getId());
        $this->assertNull($this->subscriber->getEmail());
        $this->assertNull($this->subscriber->getUnsubscribeToken());

        $this->subscriber->setEmail('aydin@hotmail.co.uk');
        $this->subscriber->setUnsubscribeToken('some-token');

        $this->assertSame('aydin@hotmail.co.uk', $this->subscriber->getEmail());
        $this->assertSame('some-token', $this->subscriber->getUnsubscribeToken());
    }
}
