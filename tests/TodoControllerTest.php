<?php

require_once('src/controllers/TodoController.php');
use PHPUnit\Framework\TestCase;
use App\Models\TodoModel;
use App\Controllers\TodoController;
use App\Test\MockTwig;
use Silex\Application;
use Silex\Provider\ValidatorServiceProvider;

class TodoControllerTest extends TestCase
{
    private $controller;
    private $mockTwig;
    private $mockModel;
    private $mockApp;

    public function setUp()
    {
        $this->mockModel = $this->getMockBuilder(TodoModel::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'getAllByUser', 'add', 'delete', 'toggleComplete'])
            ->getMock('');

        $this->mockTwig = $this->getMockBuilder(MockTwig::class)
            ->setMethods(['render'])
            ->getMock();

        $this->mockApp = $this->getMockBuilder(Application::class)
            ->setMethods(['redirect'])
            ->getMock();

        // register validator into app for test
        $validator = new ValidatorServiceProvider();
        $validator->register($this->mockApp);

        $this->mockApp['db'] = '';
        $this->mockApp['twig'] = $this->mockTwig;

        $this->controller = new TodoController($this->mockApp);
        $this->controller->setModel($this->mockModel);
    }
    public function testGet()
    {
        $this->mockModel->expects($this->once())
            ->method('get')
            ->with(1)
            ->willReturn('mockTodo');

        $this->mockTwig->expects($this->once())
            ->method('render')
            ->with('todo.html', ['todo' => 'mockTodo'])
            ->willReturn('pass');
        $this->assertEquals('pass', $this->controller->get(1));
    }

    public function testGetByUserId()
    {
        $this->mockModel->expects($this->once())
            ->method('get')
            ->with(1)
            ->willReturn('mockTodo');

        $this->mockTwig->expects($this->once())
            ->method('render')
            ->with('todo.html', ['todo' => 'mockTodo'])
            ->willReturn('pass');
        $this->assertEquals('pass', $this->controller->get(1));
    }

    public function testAdd()
    {
        $this->mockModel->expects($this->once())
            ->method('add')
            ->with(1, 'description');

        $this->mockApp->expects($this->once())
            ->method('redirect')
            ->with('/todo');

        $this->controller->add(1, 'description');
    }

    public function testAddFailWithEmptyDescription()
    {
        $this->mockModel->expects($this->never())
            ->method('add')
            ->with(1, 'description');

        $this->mockApp->expects($this->once())
            ->method('redirect')
            ->with('/todo');

        $this->controller->add(1, '');
    }

    public function testDelete()
    {
        $this->mockModel->expects($this->once())
            ->method('delete')
            ->with(999);

        $this->mockApp->expects($this->once())
            ->method('redirect')
            ->with('/todo');

        $this->controller->delete(999);
    }

    public function testToggleComplete()
    {
        $this->mockModel->expects($this->once())
            ->method('toggleComplete')
            ->with(999);

        $this->mockApp->expects($this->once())
            ->method('redirect')
            ->with('/todo');

        $this->controller->toggleComplete(999);
    }
}
