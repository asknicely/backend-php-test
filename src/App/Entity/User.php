<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Todos
 *
 * @ORM\Entity(repositoryClass="App\Entity\User.php")
 * @ORM\Table(name="users")
 * @ORM\Entity
 */
class User
{
    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\Column(name="username", type="string", nullable=false)
     */
    private $username;

    /**
     * @ORM\Column(name="password", type="string", nullable=false)
     */
    private $password;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }
}