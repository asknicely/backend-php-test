<?php

require_once('src/controllers/TodoController.php');
use PHPUnit\Framework\TestCase;
use App\Models\TodoModel;
use App\Controllers\TodoController;
use Silex\Application;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\TwigServiceProvider;

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
            ->setMethods(['getByUserIdWithPagination', 'get', 'getAllByUser', 'add', 'delete', 'toggleComplete'])
            ->getMock('');

        $this->mockTwig = $this->getMockBuilder(TwigServiceProvider::class)
            ->disableOriginalConstructor()
            ->setMethods(['render'])
            ->getMock();

        $this->mockApp = $this->getMockBuilder(Application::class)
            ->setMethods(['redirect', 'json'])
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

    public function testGetByUserIdWithPagination()
    {
        $data = [
            'todo' => [],
            'pageNum' => 10,  
            'pageTotal' => 100,
            'pageSize' => 20
        ];
        $this->mockModel->expects($this->once())
            ->method('getByUserIdWithPagination')
            ->with(1, 1, 10)
            ->willReturn($data);
        $this->mockTwig->expects($this->once())
            ->method('render')
            ->with('todos.html', $data)
            ->willReturn('pass');
        $this->controller->getByUserIdWithPagination(1, 2, 10);  
    }

    public function testGetByUserIdWithPaginationDefaultParams()
    { 
        $data = [
            'todo' => [],
            'pageNum' => 10,  
            'pageTotal' => 100,
            'pageSize' => 20
        ];
        $this->mockModel->expects($this->once())
            ->method('getByUserIdWithPagination')
            ->with(1, 0, 5)   // default should be pageNum = 0 pageSize = 5
            ->willReturn($data);
        $this->mockTwig->expects($this->once())
            ->method('render')
            ->with('todos.html', $data)
            ->willReturn('pass');
        $this->controller->getByUserIdWithPagination(1); 
    }

    public function testGetJson()
    {
        $this->mockModel->expects($this->once())
            ->method('get')
            ->with(1)
            ->willReturn(['id' => 1, 'description' => 'test']);

        $this->mockApp->expects($this->once())
            ->method('json')
            ->with(['id' => 1, 'description' => 'test'])
            ->willReturn('pass');

        $this->assertEquals('pass', $this->controller->getJson(1));
    }
}
