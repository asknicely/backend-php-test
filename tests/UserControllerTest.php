<?php

require_once('src/controllers/UserController.php');
use PHPUnit\Framework\TestCase;
use App\Controllers\UserController;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\ValidatorServiceProvider;

class UserControllerTest extends TestCase
{
    private $controller;
    private $mockTwig;
    private $mockModel;
    private $mockApp;
    private $mockSession;

    private $mockUser = ['id' => 10];

    public function setUp()
    {
        $this->mockModel = $this->getMockBuilder(UserController::class)
            ->disableOriginalConstructor()
            ->setMethods(['verify'])
            ->getMock('');

        $this->mockTwig = $this->getMockBuilder(TwigServiceProvider::class)
            ->disableOriginalConstructor()
            ->setMethods(['render'])
            ->getMock();

        $this->mockApp = $this->getMockBuilder(Application::class)
            ->setMethods(['redirect'])
            ->getMock();

        $this->mockSession = $this->getMockBuilder(SessionServiceProvider::class)
            ->setMethods(['set'])
            ->getMock();

        $this->mockApp['db'] = '';
        $this->mockApp['twig'] = $this->mockTwig;
        $this->mockApp['session'] = $this->mockSession;

        // register validator into app for test
        $validator = new ValidatorServiceProvider();
        $validator->register($this->mockApp);

        $this->controller = new UserController($this->mockApp);
        $this->controller->setModel($this->mockModel);
    }
    public function testLoginSuccess()
    {
        $this->mockModel->expects($this->once())
            ->method('verify')
            ->with('test', 'pwd')
            ->willReturn(['id' => 1]);

        $this->mockApp->expects($this->once())
            ->method('redirect')
            ->with('/todo');

        $this->controller->login('test', 'pwd');
    }

    public function testLoginFail()
    {
        $this->mockModel->expects($this->once())
            ->method('verify')
            ->with('test', 'pwd')
            ->willReturn(false);

        $this->mockApp->expects($this->never())
            ->method('redirect');

        $this->mockTwig->expects($this->once())
            ->method('render')
            ->with('login.html');

        $this->controller->login('test', 'pwd');
    }

    public function testLogout()
    {

        $this->mockSession->expects($this->once())
            ->method('set')
            ->with('user', null);

        $this->mockApp['session'] = $this->mockSession;

        $this->controller = new UserController($this->mockApp);
        $this->controller->logout();
    }
    public function testEmptyUserName()
    {
        $this->mockModel->expects($this->never())
            ->method('verify');

        $this->mockApp->expects($this->never())
            ->method('redirect');

        $this->mockTwig->expects($this->once())
            ->method('render')
            ->with('login.html');

        $this->controller->login('', 'pwd');
    }
    public function testEmptyPassword()
    {
        $this->mockModel->expects($this->never())
            ->method('verify');

        $this->mockApp->expects($this->never())
            ->method('redirect');

        $this->mockTwig->expects($this->once())
            ->method('render')
            ->with('login.html');

        $this->controller->login('test', '');
    }
    public function testSpaceUserName()
    {
        $this->mockModel->expects($this->never())
            ->method('verify');

        $this->mockApp->expects($this->never())
            ->method('redirect');

        $this->mockTwig->expects($this->once())
            ->method('render')
            ->with('login.html');

        $this->controller->login('   ', 'pwd');
    }
    public function testSpacePassword()
    {
        $this->mockModel->expects($this->never())
            ->method('verify');

        $this->mockApp->expects($this->never())
            ->method('redirect');

        $this->mockTwig->expects($this->once())
            ->method('render')
            ->with('login.html');

        $this->controller->login('test', '   ');
    }
}
