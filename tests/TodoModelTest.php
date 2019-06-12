<?php

require_once('src/models/TodoModel.php');
use PHPUnit\Framework\TestCase;
use App\Test\MockDB;
use App\Models\TodoModel;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Driver\DrizzlePDOMySql\Connection;

class TodoModelTest extends TestCase
{
    public function testGet()
    {

        $mockDB = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
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
        $mockDB = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
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
        $mockDB = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->setMethods(['insert', 'lastInsertId'])
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
        $mockDB = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
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

    public function testGetByUserIdWithPagination()
    {
        $user_id = 10;
        $offset = 4;
        $pageSize = 2;
        $mockQB = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['select', 'from', 'where', 'setParameter', 'setFirstResult', 'setMaxResults', 'execute'])
            ->getMock();

        $mockQB->expects($this->at(0))
            ->method('select')
            ->with('*')
            ->willReturn($mockQB);

        $mockQB->expects($this->at(1))
            ->method('from')
            ->with(TodoModel::TABLE)
            ->willReturn($mockQB);

        $mockQB->expects($this->at(2))
            ->method('where')
            ->with('user_id = :user_id')
            ->willReturn($mockQB);

        $mockQB->expects($this->at(3))
            ->method('setParameter')
            ->with(':user_id', $user_id)
            ->willReturn($mockQB);

        $mockQB->expects($this->at(4))
            ->method('setFirstResult')
            ->with($offset)
            ->willReturn($mockQB);

        $mockQB->expects($this->at(5))
            ->method('setMaxResults')
            ->with($pageSize)
            ->willReturn($mockQB);

        $mockQB->expects($this->at(6))
            ->method('execute')
            ->willReturn(true);


        $mockDB = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->setMethods(['createQueryBuilder'])
            ->getMock();
        $mockDB->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($mockQB);

        $todoInst = new TodoModel($mockDB);
        $todoInst->mockGetTodoTotal = 10;
        $todoInst->getByUserIdWithPagination($user_id, 2, 2);
    }

    public function testToggleComplete()
    {

        $id = 88;
        $mockQB = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['update', 'set', 'where', 'setParameter', 'execute'])
            ->getMock();

        $mockQB->expects($this->at(0))
            ->method('update')
            ->with(TodoModel::TABLE)
            ->willReturn($mockQB);

        $mockQB->expects($this->at(1))
            ->method('set')
            ->with('completed', '!completed')
            ->willReturn($mockQB);

        $mockQB->expects($this->at(2))
            ->method('where')
            ->with('id = :id')
            ->willReturn($mockQB);

        $mockQB->expects($this->at(3))
            ->method('setParameter')
            ->with(':id', $id)
            ->willReturn($mockQB);

        $mockQB->expects($this->at(4))
            ->method('execute')
            ->willReturn(true);


        $mockDB = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->setMethods(['createQueryBuilder'])
            ->getMock();

        $mockDB->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($mockQB);

        $todoInst = new TodoModel($mockDB);
        $todoInst->toggleComplete($id);
    }
}
