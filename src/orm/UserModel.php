<?php

namespace AsknicelyORM;


class UserModel extends AbstractModel
{
    private $id;
    private $username;
    private $password;

    protected $table = 'users';
    
    public function login($user, $password) {
        self::$instance = null;
        $id = $this->getData($user, $password);
        return  self::getInstance($id);
    }

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
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

}