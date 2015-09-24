<?php

namespace MikeyMike\RfcDigestor\Entity;

/**
 * Class Subscriber
 * @package MikeyMike\RfcDigestor\Entity
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */

/**
 * @Entity @Table(name="slack_subscriber")
 **/
class SlackSubscriber
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     **/
    protected $id;

    /**
     * @Column(type="string")
     **/
    protected $email;

    /**
     * @Column(type="string")
     **/
    protected $token;

    /**
     * @Column(type="string")
     **/
    protected $channel;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $channel
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }
}
