<?php

require_once('src/models/UserModel.php');
use PHPUnit\Framework\TestCase;
use App\Models\UserModel;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Driver\DrizzlePDOMySql\Connection;

class UserModelTest extends TestCase
{
    public function testVerify()
    {
        $mockQB = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['select', 'from', 'where', 'andWhere', 'setParameter', 'execute', 'fetch'])
            ->getMock();

        $mockQB->expects($this->at(0))
            ->method('select')
            ->with('*')
            ->willReturn($mockQB);

        $mockQB->expects($this->at(1))
            ->method('from')
            ->with(UserModel::TABLE)
            ->willReturn($mockQB);
        $mockQB->expects($this->at(2))
            ->method('where')
            ->with('username = :username')
            ->willReturn($mockQB);
        $mockQB->expects($this->at(3))
            ->method('andWhere')
            ->with('password = :password')
            ->willReturn($mockQB);

        $mockQB->expects($this->exactly(2))
            ->method('setParameter')
            ->withConsecutive(
                [$this->equalTo(':username'), $this->equalTo('test')],
                [$this->equalTo(':password'), $this->equalTo('pwd')],
            )
            ->willReturn($mockQB);

        $mockQB->expects($this->at(6))
            ->method('execute')
            ->willReturn($mockQB);

        $mockQB->expects($this->at(7))
            ->method('fetch')
            ->willReturn(['t']);

        $mockDB = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->setMethods(['createQueryBuilder'])
            ->getMock();

        $mockDB->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($mockQB);

        $todoInst = new UserModel($mockDB);
        $todoInst->verify('test', 'pwd');
    }
}
