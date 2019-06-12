<?php
namespace App\Models;

class UserModel
{
    protected $db;

    public $mockGetTodoTotal; // mock point for function getTodoTotal

    const TABLE = 'users';

    public function __construct($db)
    {
        $this->db = $db;
    }
    public function verify($usrName, $pwd)
    {
        $data = $this->db->createQueryBuilder()
        ->select('*')
        ->from(self::TABLE)
        ->where('username = :username')
        ->andWhere('password = :password')
        ->setParameter(':username', $usrName)
        ->setParameter(':password', md5($pwd))
        ->execute()
        ->fetch();
        return count($data) > 0 ? $data :  false;
    }
}