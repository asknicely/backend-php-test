<?php

use Symfony\Component\Yaml\Yaml;

class Model {
    private $db;
    private $table_name;

    public function __construct($db, $table_name) {
        $this->db = $db;
        $this->table_name = $table_name;
    }

    public function findBySql($sql, $conditions=[]) {
        try {
            if ($conditions) {
                $records = $this->db->fetchAssoc($sql, $conditions);
            } else {
                $records = $this->db->fetchAssoc($sql);
            }
            return $records;
        } catch (\Exception $e) {
            return false;
        }
    }


    public function findAllBySql($sql, $conditions=[]) {
        try {
            if ($conditions) {
                $records = $this->db->fetchAll($sql, $conditions);
            } else {
                $records = $this->db->fetchAll($sql);
            }
            return $records;
        } catch (\Exception $e) {
            return false;
        }
    }


    public function findAll($conditions = false, $limit = false) {
        try {
            $where = [];
            $values = [];
            $sql = "SELECT * FROM {$this->table_name} ";
            if (is_array($conditions)) {
                foreach ($conditions as $key=>$val) {
                    if ($where) {
                        $where .= " AND {$key} = ? ";
                    } else {
                        $where = " {$key} = ? ";
                    }
                    $values[] = $val;
                }
                $sql = "SELECT * FROM {$this->table_name} WHERE {$where} ";
            } elseif (false !== $conditions) {
                $conditions = intval($conditions);
                $sql = "SELECT * FROM {$this->table_name} WHERE id = ? ";
                $values = [$conditions];
            }
            if ($limit) {
                $sql .= " {$limit}";
            }
            $records = $this->findAllBySql($sql, $values);
            return $records;
        }catch (\Exception $e) {
            return false;    
        }
    }

    public function rowCount($conditions=false) {
        try {
            $where = [];
            $values = [];
            $sql = "SELECT COUNT(1) FROM {$this->table_name} ";

            if (is_array($conditions)) {
                foreach ($conditions as $key=>$val) {
                    if ($where) {
                        $where .= " AND {$key} = ? ";
                    } else {
                        $where = " {$key} = ? ";
                    }
                    $values[] = $val;
                }
                $sql = "SELECT COUNT(1) AS cnt FROM {$this->table_name} WHERE {$where} ";
            } elseif (false !== $conditions) {
                $conditions = intval($conditions);
                $sql = "SELECT COUNT(1) AS cnt FROM {$this->table_name} WHERE id = ? ";
                $values = [$conditions];
            }
            $row = $this->findBySql($sql, $values);
            if (is_array($row)) {
                return intval($row['cnt']);    
            }
            return false;
        }catch (\Exception $e) {
            return false;    
        }
    }


    public function findOne($conditions) {
        try {
            if (is_array($conditions)) {
                $where = '';
                $values = [];
                foreach ($conditions as $key=>$val) {
                    if ($where) {
                        $where .= " AND {$key} = ? ";
                    } else {
                        $where = " {$key} = ? ";
                    }
                    $values[] = $val;
                }
                $sql = "SELECT * FROM {$this->table_name} WHERE {$where} LIMIT 1";
            } else {
                $conditions = intval($conditions);
                $sql = "SELECT * FROM {$this->table_name} WHERE id = ? LIMIT 1";
                $values = [$conditions];
            }
            $records = $this->findBySql($sql, $values);
            return $records;
        }catch (\Exception $e) {
            return false;    
        }
    }


    public function deleteOne($conditions) {
        $where = [];
        $values = [];
        foreach ($conditions as $key=>$val) {
            if ($where) {
                $where .= " AND {$key} = ? ";
            } else {
                $where = " {$key} = ? ";
            }
            $values[] = $val;
        }

        $sql = "DELETE FROM {$this->table_name} WHERE {$where} LIMIT 1";
        $cnt = $this->db->executeUpdate($sql, $values);
        return $cnt;
    }

    public function insertOne($conditions) {
        if (empty($conditions)) {
            return false;    
        }
        $fields = [];
        $values = [];
        $marks  = [];
        foreach ($conditions as $key=>$val) {
            $fields[] = $key;
            $values[] = $val;
            $marks[] = '?';
        }
        $fields = implode(', ', $fields);
        $marks = implode(', ', $marks);

        $sql = "INSERT INTO {$this->table_name} ($fields) VALUES ({$marks})";
        $cnt = $this->db->executeUpdate($sql, $values);
        return $cnt;
    }

    public function updateAll($updates, $conditions) {
        if (empty($conditions)) {
            return false;    
        }
        $sets = [];
        foreach ($updates as $key=>$val) {
            if(is_int($val)) {
                $sets[] = "{$key} = {$val}";
            } elseif(is_string($val)) {
                $sets[] = "{$key} = '{$val}'";
            } elseif(is_bool($val)) {
                $sets[] = "{$key} = {$val}";
            } else {
                $sets[] = "{$key} = {$val}";
            }
        }
        $sets = implode(',', $sets);

        $where = [];
        foreach ($conditions as $key=>$val) {
            if(is_int($val)) {
                $value = $val;
            } elseif(is_string($val)) {
                $value = "'{$val}'";
            } elseif(is_bool($val)) {
                $value = $val;
            } else {
                $value = $val;
            }

            if ($where) {
                $where .= " AND {$key} =  {$value}";
            } else {
                $where = " {$key} = {$value} ";
            }
        }

        $sql = "UPDATE {$this->table_name} SET $sets WHERE {$where}";
        $cnt = $this->db->executeUpdate($sql);
        return $cnt;
    }

}
