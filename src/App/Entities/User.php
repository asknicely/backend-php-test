<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table(name="users")
 * @ORM\Entity
 */
class User
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=255)
     */
    public $username;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="hash_type", type="string")
     */
    private $hash_type;

    /**
     * @var string
     *
     * @ORM\Column(name="salt", type="string")
     */
    private $salt;

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getHashType()
    {
        return $this->hash_type;
    }

    /**
     * @param string $hash_type
     */
    public function setHashType($hash_type)
    {
        $this->hash_type = $hash_type;
    }

    /**
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * @param string $salt
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
    }

    public function toArray()
    {
        return array(
            'id' => $this->id,
            'username' => $this->username
        );
    }
}