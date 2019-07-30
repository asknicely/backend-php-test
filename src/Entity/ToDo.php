<?php

namespace Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @Entity
 * @Table(name="todos")
 */
class ToDo
{
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
}