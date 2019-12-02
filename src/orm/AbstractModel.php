<?php

namespace AsknicelyORM;
use PDO;

class AbstractModel extends PdoMysql{
    
    static protected $instance = null;
    
    protected $insert = [];
    
    static public function getInstance($id = null){
        if (self::$instance == null){
            self::$instance = new static($id);
        }
        return self::$instance;
    }
    
    public function __construct($id){
        
        parent::__construc();
        if(is_numeric($id)){
            $data = $this->getById($id);
            if($data == null)
                return;
            foreach ($data as $key => $value){
                $method = 'set'.ucfirst($key);
                if(method_exists($this, $method)){
                    $this->$method($value);
                }
            }
            return $this;
        }
    }

    protected function getById($id){
        $stmt = $this->pdo->prepare("SELECT * FROM ".$this->table." WHERE id = :id");
        
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        $stmt->execute();
        while ($row = $stmt->fetch()){
            return $row;
        }
    }
    
    protected function getData($username, $password) {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE username = :username AND password = :password ');
        
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        
        $stmt->execute();
        while ($row = $stmt->fetch()){
            return $row['id'];
        }
    }

    public function where($field, $value) {
        $stmt = $this->pdo->prepare("SELECT * FROM $this->table WHERE $field = :value");
        
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        
        $stmt->bindParam(':value', $value, PDO::PARAM_STR);
        
        $stmt->execute();
        while ($row = $stmt->fetch()){
            return $row;
        }
    }

    public function count($field, $value) {
        $stmt = $this->pdo->prepare("SELECT count(*) as n FROM $this->table WHERE $field = :value");
        
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        
        $stmt->bindParam(':value', $value, PDO::PARAM_STR);
        
        $stmt->execute();
        while ($row = $stmt->fetch()){
            return $row;
        }
    }

    public function paginate($field, $value, $orderBy, $from, $itemsPerPage ){

        $sql ="SELECT * FROM $this->table WHERE $field = $value ORDER BY $orderBy desc LIMIT $from, $itemsPerPage";
        // echo $sql; exit();
        $stmt = $this->pdo->prepare($sql);
        
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        
        $stmt->bindParam(':value', $value, PDO::PARAM_STR);
        
        $stmt->execute();
        $out = [];
        while ($row = $stmt->fetch()){
            array_push($out, $row);
        }
        return $out;
    }

    public function insert(){
        $númargs = func_num_args();
        $arg_list = func_get_args();
        $this->insert['fields'] = $arg_list;
        return $this;
    }

    public function values(){

        $númargs = func_num_args();

        $arg_list = func_get_args();

        $this->insert['values'] = $arg_list;

        $sql = "INSERT INTO $this->table (". implode(', ', $this->insert['fields'] )
                .") VALUES ('". implode("', '", $this->insert['values'] )."')";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute();
    }

    public function save(){
        
        $id = $this->getId();

        $completed  = $this->getCompleted();

        $sql = "UPDATE $this->table SET completed = '" 
            . $completed . "' WHERE id = '" . $id  . "'";
        
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute();
    }

    public function delete(){

        $id = $this->getId();

        $sql = "DELETE FROM $this->table WHERE id = '$id'";
        
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute();
    }
    
    
}