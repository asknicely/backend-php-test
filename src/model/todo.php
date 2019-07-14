<?php

namespace App\Model;

class TodoModel
{
    protected $db;
    const TABLE = 'todos';

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function get($id)
    {
        // retrieve a single todo record
        return $this->db->fetchAssoc('SELECT * FROM ' . self::TABLE . ' WHERE id = ?', array($id));
    }

    public function getCount($id)
    {
        // retrieve a single todo record
        return ($this->db->fetchAssoc('SELECT count(id) as total_todos FROM ' . self::TABLE . ' WHERE user_id = ?', array($id)))['total_todos'];
    }

    public function getAllbyUser($userId, $offset, $limit)
    {
        // return all todo records for this user
        return $this->db->fetchAll('SELECT * FROM ' . self::TABLE . ' WHERE user_id = ? LIMIT ' . $offset . ',' . $limit, array($userId));
    }

    public function add($userId, $description)
    {
        // insert new todo
        $this->db->insert(self::TABLE, array(
            'user_id' => $userId,
            'description' => $description
        ));
        return $this->db->lastInsertId();
    }

    public function setAsCompleted($id)
    {
        $this->db->update(self::TABLE,
            array('completed' => true),
            array('id' => $id));
        return $this->db->lastInsertId();
    }

    public function delete($id)
    {
        // DELETE FROM todos WHERE id = ? ($id)
        $this->db->delete(self::TABLE, array(
            'id' => $id
        ));
    }
}