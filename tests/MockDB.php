<?php
namespace App\Test;
use Doctrine\DBAL\Connection;

class MockDB extends \Doctrine\DBAL\Connection{

    public function fetchAssoc($stat, $params) {

    }

    public function fetchAll($sql, $params = [], $types = [])
    {
        
    }

    public function insert($table, $params) {
       
    }

    public function delete($table, $params) {
    }

    public function lastInsertId(){
        
    }
}