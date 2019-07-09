<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Todos
 *
 * @ORM\Entity(repositoryClass="App\Entity\Todo.php")
 * @ORM\Table(name="todos")
 * @ORM\Entity
 */
class Todo
{
    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $userId;

    /**
     * @ORM\Column(name="description", type="string", length=255, nullable=false)
     */
    private $description;

    /**
     * @ORM\Column(name="status", type="string", nullable=false)
     */
    private $status;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser_Id()
    {
        return $this->userId;
    }

    public function setUser_Id($userId)
    {
        $this->userId = $userId;
        return $this;
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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus($status): self
    {
        $this->status = $status;
        return $this;
    }

    public function serializer(): string
    {
        return json_encode(
          [
              'id' => $this->id,
              'user_id' => $this->userId,
              'description' => $this->description,
              'status' => $this->status,
          ]
        );
    }
}