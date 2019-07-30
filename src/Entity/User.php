<?php

namespace Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity()
 * @Table(name="users")
 */
class User
{
    /**
     * @Column(name="id", type="integer")
     * @Id
     * @GeneratedValue(strategy="AUTO")
     */
    private
        $id;

    /**
     * @Column(type="string", length=255, name="username")
     */
    private
        $userName;

    /**
     * @Column(type="string", length=255, name="password")
     */
    private
        $passWord;

    /**
     * @OneToMany(targetEntity="ToDo", mappedBy="author", cascade={"all"})
     */
    public $todos;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @param mixed $userName
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;
    }

    /**
     * @return mixed
     */
    public function getTodos()
    {
        return $this->todos;
    }

    public function addTodos(ToDo $t)
    {
        return $this->todos[] = $t;
    }

    /**
     * @return mixed
     */
    public function getPassWord()
    {
        return $this->passWord;
    }

    /**
     * @param mixed $passWord
     */
    public function setPassWord($passWord)
    {
        $this->passWord = $passWord;
    }

    public function __construct()
    {
        $this->todos = new ArrayCollection();
    }
}