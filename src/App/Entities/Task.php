<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * App\Entities\Task
 * @ORM\Entity
 * @ORM\Table(name="todos")
 */
class Task
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
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    public $user_id;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    public $description;

    /**
     * @var int
     *
     * @ORM\Column(name="completed", type="integer")
     */
    public $completed;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return int
     */
    public function getCompleted()
    {
        return $this->completed;
    }

    /**
     * @param int $completed
     */
    public function setCompleted($completed)
    {
        $this->completed = $completed;
    }

}