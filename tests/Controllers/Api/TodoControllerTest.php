<?php
use PHPUnit\Framework\TestCase;
use \Mockery as m;
use Controllers\Api\TodoController;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class TodoControllerTest extends TestCase
{
    public function testCanBeCreatedFromValidEmailAddress(): void
    {
        // mock dependencies
        $connection = m::mock(Connection::class);
        $connection->shouldReceive('executeUpdate')
            ->andReturn(true);

        $session = m::mock(Session::class);
        $session->shouldReceive('get')
            ->with('user')
            ->andReturn([
                'id' => 1
            ]);

        $controller = new TodoController($connection, $session);

        $result = $controller->delete(1);

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(Response::HTTP_OK, $result->getStatusCode());
    }
}
