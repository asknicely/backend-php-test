<?php
namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * Todos
 *
 * @ORM\Table(name="todos", indexes={@ORM\Index(name="user_id", columns={"user_id"})})
 * @ORM\Entity
 */
class Todos
{
    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(name="completed", type="boolean", nullable=true)
     */
    private $completed;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Entity\Users", inversedBy="todos", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    public function getId(): int
    {
        return $this->id;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function isCompleted(): bool
    {
        return $this->completed === true;
    }

    public function setCompleted(bool $completed): self
    {
        $this->completed = $completed;
        return $this;
    }

    public function getUser(): Users
    {
        return $this->user;
    }

    public function setUser(Users $user): self
    {
        $this->user = $user;
        return $this;
    }
}
