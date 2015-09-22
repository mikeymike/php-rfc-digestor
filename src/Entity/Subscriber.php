<?php

namespace MikeyMike\RfcDigestor\Entity;

/**
 * Class Subscriber
 * @package MikeyMike\RfcDigestor\Entity
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */

/**
 * @Entity @Table(name="subscriber")
 **/
class Subscriber
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     **/
    protected $id;

    /**
     * @Column(type="string", unique=true)
     **/
    protected $email;

    /**
     * @Column(type="string")
     **/
    protected $unsubscribeToken;

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
    public function setUnsubscribeToken($token)
    {
        $this->unsubscribeToken = $token;
    }

    /**
     * @return string
     */
    public function getUnsubscribeToken()
    {
        return $this->unsubscribeToken;
    }
}
