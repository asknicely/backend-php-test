<?php

namespace AsknicelyORM;
use PDO;

class PdoMysql
{
    private $servername = "localhost:3308";
    private $username = "root";
    private $password = "secret";
    public $pdo;

    public function __construc(){
        $this->run();
    }

    public function run(){
        // Create connection
        try {
            $this->pdo = new PDO("mysql:host=localhost:3308;dbname=ac_todos", 'root', 'secret');
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // echo "Connected successfully";
        }catch(PDOException $e){
            // echo "Connection failed: " . $e->getMessage();
        }
    }
}