<?php

namespace Models;

class Todo
{

    /**
     * Getting single todo by id
     */
    public function getById($id)
    {
        return $this->queryBuilder
            ->select('id', 'user_id', 'description', 'completed')
            ->from('todos')
            ->where('id = ?')
            ->setParameter(0, $id)
            ->execute()
            ->fetch();
    }

    /**
     * Getting all todos from currently logged user
     */
    public function getAllTodosFromCurrentUser($userId, $perPage, $currentPage)
    {
        return $this->queryBuilder
            ->select('id', 'user_id', 'description', 'completed')
            ->from('todos')
            ->where('user_id = ?')
            ->setParameter(0, $userId)
            ->setFirstResult($perPage * $currentPage - $perPage)
            ->setMaxResults($perPage)
            ->execute()
            ->fetchAll();
    }

    /**
     * Adding a new todo to db
     */
    public function insert($data)
    {
        $this->queryBuilder->insert('todos')
            ->values(
                array(
                    'user_id' => '?',
                    'description' => '?'
                )
            )
            ->setParameter(0, $data['user_id'])
            ->setParameter(1, $data['description'])
            ->execute();
    }

    /**
     * Deleting a todo from db
     */
    public function delete($id)
    {
        $this->queryBuilder
            ->delete('todos')
            ->where('id = ?')
            ->setParameter(0, $id)
            ->execute();
    }

    /**
     * Getting a count of all todos by user id
     */
    public function countByCurrentUser($userId)
    {
        return $this->queryBuilder
            ->select('id')
            ->from('todos')
            ->where('user_id = ?')
            ->setParameter(0, $userId)
            ->execute()
            ->rowCount();
    }

    /**
     * Inserting 0 or 1 to completed column for todo by id
     */
    public function complete($id)
    {
        $this->queryBuilder
            ->update('todos')
            ->set('completed', '?')
            ->where('id = ?')
            ->setParameter(0, 1)
            ->setParameter(1, $id)
            ->execute();
    }

}