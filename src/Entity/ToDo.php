<?php

namespace Entity;

use Doctrine\ORM\Mapping as ORM;
use Psr\Log\InvalidArgumentException;

/**
 * @Entity
 * @Table(name="todos")
 */
class ToDo
{
    const ISDONE = 1;
    const ONGOING = 0;

    /**
     * @Column(type="integer")
     * @Id
     * @GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

//    /**
//     * @Column(type="integer", name="user_id")
//     */
//    private $userId;

    /**
     * @Column(type="string", length=255, name="description")
     */
    private $description;

    /**
     * @ManyToOne(targetEntity="User", inversedBy="todos", cascade={"persist"})
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $author;

    /**
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param mixed $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @Column(type="integer", name="is_done", options={"default":0})
     */
    private $isDone = 0;

    /**
     * @param mixed $isDone
     */
    public function setIsDone($isDone)
    {
        if (!in_array($isDone, array(self::ISDONE, self::ONGOING))) {
            throw new InvalidArgumentException("Invalid done status");
        }
        $this->isDone = $isDone;
    }

    /**
     * @return mixed
     */
    public function hasDone()
    {
        return $this->isDone == 1;
    }


}