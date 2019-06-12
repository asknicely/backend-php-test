<?php
namespace App\Models;

use Doctrine\DBAL\Connection;

class TodoModel
{
    protected $db;

    public $mockGetTodoTotal; // mock point for function getTodoTotal

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

    public function toggleComplete($id)
    {
        // UPDATE todos set completed = !completed WHERE id = ? ($id)
        $this->db->createQueryBuilder()
            ->update(self::TABLE)
            ->set('completed', '!completed')
            ->where('id = :id')
            ->setParameter(':id', $id)
            ->execute();
    }

    // TODO need to handle count with COUNT(*)
    private function getTodoTotal($userId) {
        if($this->mockGetTodoTotal != null) {
            return $this->mockGetTodoTotal;
        }
        return count($this->getAllbyUser($userId));
    }

    public function getByUserIdWithPagination(int $userId, int $pageNum, int $pageSize)
    {
        $pageTotal = ceil($this->getTodoTotal($userId) / $pageSize); 
        $offset = $pageSize * $pageNum;
        $data = $this->db->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE)
            ->where('user_id = :user_id')
            ->setParameter(':user_id', $userId)
            ->setFirstResult($offset)
            ->setMaxResults($pageSize)
            ->execute();
        return [ 
            'todos' => $data,
            'pageNum' => $pageNum + 1, // in front end the start page is 1 
            'pageTotal' => $pageTotal,
            'pageSize' => $pageSize
        ];
    }
}
