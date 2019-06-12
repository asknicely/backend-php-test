<?php
namespace App\Models;

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
        return $this->db->fetchAssoc('SELECT * FROM todos WHERE id = ?', array($id));
    }
    public function getAllbyUser($userId)
    {
        // return all todo records for this user
        return $this->db->fetchAll('SELECT * FROM todos WHERE user_id = ?', array($userId));
    }

    public function add($userId, $description)
    {
        // INSERT INTO todos (user_id, description) VALUES (?, ?) ($userId, $description) 
        $this->db->insert(self::TABLE, array(
            'user_id' => $userId,
            'description' => $description
        ));
        return $this->db->lastInsertId();
    }
    public function delete($id)
    {
        // DELETE FROM todos WHERE id = ? ($id)
        $this->db->delete(self::TABLE, array(
            'id' => $id
        ));
    }

    public function toggleComplete($id){
        // UPDATE todos set completed = !completed WHERE id = ? ($id)
        $this->db->createQueryBuilder()
        ->update(self::TABLE)
        ->set('completed', '!completed')
        ->where('id = :id')
        ->setParameter(':id', $id)
        ->execute();
    }
}
