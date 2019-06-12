<?php
namespace App\Models;

class TodoModel
{
    protected $db;

    public $mockGetTodoTotal; // mock point for function getTodoTotal

    const TABLE = 'todos';


    public function __construct($db)
    {
        $this->db = $db;
    }
    public function get($usrId, $id)
    {
        // retrieve a single todo record
        return $this->db->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE)
            ->where('user_id = :user_id')
            ->andWhere('id = :id')
            ->setParameter(':user_id', $usrId)
            ->setParameter(':id', $id)
            ->execute()
            ->fetch();
    }
    public function getAllbyUser($usrId)
    {
        // return all todo records for this user
        return $this->db->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE)
            ->where('user_id = :user_id')
            ->setParameter(':user_id', $usrId)
            ->execute()
            ->fetch();
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
    public function delete($usrId, $id)
    {
        // DELETE FROM todos WHERE id = ? ($id)
        $this->db->delete(self::TABLE, array(
            'id' => $id,
            'user_id' => $usrId
        ));
    }

    public function toggleComplete($userId, $id)
    {
        // UPDATE todos set completed = !completed WHERE id = ? ($id)
        $this->db->createQueryBuilder()
            ->update(self::TABLE)
            ->set('completed', '!completed')
            ->where('id = :id')
            ->andWhere('user_id = :user_id')
            ->setParameter(':id', $id)
            ->setParameter(':user_id', $userId)
            ->execute();
    }

    private function getTodoTotal($usrId)
    {
        if ($this->mockGetTodoTotal != null) {
            return $this->mockGetTodoTotal;
        }
        return $this->db->createQueryBuilder()
            ->select('count(*)')
            ->from(self::TABLE)
            ->where('user_id = :user_id')
            ->setParameter(':user_id', $usrId)
            ->execute()
            ->fetchColumn(0);
    }

    public function getByUserIdWithPagination($userId, int $pageNum, int $pageSize)
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
