<?php

namespace Models;

class Todo
{

    public function getById($id)
    {
        return $this->queryBuilder
            ->select('id', 'user_id', 'description')
            ->from('todos')
            ->where('id = ?')
            ->setParameter(0, $id)
            ->execute()
            ->fetch();
    }

    public function getAllTodosFromCurrentUser($userId)
    {
        return $this->queryBuilder
            ->select('id', 'user_id', 'description')
            ->from('todos')
            ->where('user_id = ?')
            ->setParameter(0, $userId)
            ->execute()
            ->fetchAll();
    }

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

    public function delete($id)
    {
        $this->queryBuilder->delete('todos')->where('id = ' . $id)->execute();
    }
}