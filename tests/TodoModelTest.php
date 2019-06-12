<?php

require_once('src/models/TodoModel.php');
use PHPUnit\Framework\TestCase;
use App\Test\MockDB;
use App\Models\TodoModel;

class TodoModelTest extends TestCase
{
    public function testGet()
    {
        $mockDB = $this->getMockBuilder(MockDB::class)
            ->setMethods(['fetchAssoc'])
            ->getMock();


        $mockDB->expects($this->once())
            ->method('fetchAssoc')
            ->with('SELECT * FROM todos WHERE id = ?', [1]);

        $todoInst = new TodoModel($mockDB);
        $todoInst->get(1);
    }

    public function testGetAllbyUser()
    {
        $mockDB = $this->getMockBuilder(MockDB::class)
            ->setMethods(['fetchAll'])
            ->getMock();


        $mockDB->expects($this->once())
            ->method('fetchAll')
            ->with(
                'SELECT * FROM todos WHERE user_id = ?',
                [2]
            );

        $todoInst = new TodoModel($mockDB);
        $todoInst->getAllbyUser(2);
    }

    public function testAdd()
    {
        $mockDB = $this->getMockBuilder(MockDB::class)
            ->setMethods(['insert','lastInsertId'])
            ->getMock();
        $expectedTodo = [
            'user_id' => 3,
            'description' => 'test'
        ];
        $mockDB->expects($this->once())
            ->method('insert')
            ->with(
                TodoModel::TABLE,
                $expectedTodo
            )
            ->willReturn(4);

        $mockDB->expects($this->once())
            ->method('lastInsertId')
            ->willReturn(5);

        $todoInst = new TodoModel($mockDB);
        $this->assertEquals(5, $todoInst->add(3, 'test'));
    }

    public function testDelete()
    {
        $mockDB = $this->getMockBuilder(MockDB::class)
            ->setMethods(['delete'])
            ->getMock();

        $mockDB->expects($this->once())
            ->method('delete')
            ->with(
                TodoModel::TABLE,
                ['id' => 5]
            )
            ->willReturn(4);

        $todoInst = new TodoModel($mockDB);
        $todoInst->delete(5);
    }
}
