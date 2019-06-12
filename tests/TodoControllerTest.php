<?php

require_once('src/controllers/TodoController.php');
use PHPUnit\Framework\TestCase;
use App\Models\TodoModel;
use App\Controllers\TodoController;
use Silex\Application;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

class TodoControllerTest extends TestCase
{
    private $controller;
    private $mockTwig;
    private $mockModel;
    private $mockApp;
    private $mockSession;

    private $mockUser = ['id' => 10];

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

        $this->mockSession = $this->getMockBuilder(SessionServiceProvider::class)
            ->setMethods(['getFlashBag', 'get'])
            ->getMock();

        $this->mockSession->method('get')->willReturn($this->mockUser);

        // register validator into app for test
        $validator = new ValidatorServiceProvider();
        $validator->register($this->mockApp);

        $this->mockApp['db'] = '';
        $this->mockApp['twig'] = $this->mockTwig;
        $this->mockApp['session'] = $this->mockSession;

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
            ->with(10, 'description');

        $this->mockApp->expects($this->once())
            ->method('redirect')
            ->with('/todo');

        $mockFlashBag = $this->getMockBuilder(FlashBag::class)
            ->setMethods(['add'])
            ->getMock();
        $this->mockSession->expects($this->once())
            ->method('getFlashBag')
            ->willReturn($mockFlashBag);

        $mockFlashBag->expects($this->once())
            ->method('add')
            ->with('notice', 'add success');

        $this->controller->add('description');
    }

    public function testAddFailWithEmptyDescription()
    {
        $this->mockModel->expects($this->never())
            ->method('add')
            ->with(10, 'description');

        $this->mockApp->expects($this->once())
            ->method('redirect')
            ->with('/todo');

        $this->controller->add('');
    }

    public function testDelete()
    {
        $this->mockModel->expects($this->once())
            ->method('delete')
            ->with(999);

        $this->mockApp->expects($this->once())
            ->method('redirect')
            ->with('/todo');

        $mockFlashBag = $this->getMockBuilder(FlashBag::class)
            ->setMethods(['add'])
            ->getMock();
        $this->mockSession->expects($this->once())
            ->method('getFlashBag')
            ->willReturn($mockFlashBag);

        $mockFlashBag->expects($this->once())
            ->method('add')
            ->with('notice', 'delete success');

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
            ->with(10, 1, 10)
            ->willReturn($data);
        $this->mockTwig->expects($this->once())
            ->method('render')
            ->with('todos.html', $data)
            ->willReturn('pass');
        $this->controller->getByUserIdWithPagination(2, 10);
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
            ->with(10, 0, 5)   // default should be pageNum = 0 pageSize = 5
            ->willReturn($data);
        $this->mockTwig->expects($this->once())
            ->method('render')
            ->with('todos.html', $data)
            ->willReturn('pass');
        $this->controller->getByUserIdWithPagination();
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

    public function testRedirectIfUnAuthenticated()
    {
        $this->mockSession = $this->getMockBuilder(SessionServiceProvider::class)
            ->setMethods(['getFlashBag', 'get'])
            ->getMock();
        $this->mockSession->method('get')->willReturn(null);
        $this->mockApp['session'] = $this->mockSession;

        $this->controller = new TodoController($this->mockApp);
        $this->mockApp->expects($this->exactly(7))
            ->method('redirect')
            ->with('/login');

        $this->controller->get(1);
        $this->controller->getJson(1);
        $this->controller->getByUserId(1);
        $this->controller->add('test');
        $this->controller->delete(1);
        $this->controller->toggleComplete(1);
        $this->controller->getByUserIdWithPagination();
    }
}
