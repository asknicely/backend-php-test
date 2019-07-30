<?php

use PHPUnit\Framework\TestCase;
use Silex\Application;
use DerAlex\Silex\YamlConfigServiceProvider;
use Illuminate\Database\Capsule\Manager as Capsule;

use App\User;
use App\Todo;

class TodoUnitTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        // TODO::refactory
        $app = new Application();
        $app->register(new YamlConfigServiceProvider(__DIR__ . '/../config/config.yml'));

        $capsule = new Capsule;
        $capsule->addConnection([
            "driver" => "mysql",
            "host" => $app['config']['database']['host'],
            "database" => $app['config']['database']['dbname'],
            "username" => $app['config']['database']['user'],
            "password" => $app['config']['database']['password']
        ]);

        $capsule->bootEloquent();
    }

    public function testCantSeeOthers()
    {
        $testUserA = User::find(1);
        $testUserB = User::find(2);

        $testUserBtask = $testUserB->todos->first();

        $result = $testUserA->todos
            ->find($testUserBtask->id);

        $this->assertEquals(false, $result);
    }

    public function testTodoCRUD()
    {
        $testUserA = User::find(1);

        // Create new task
        $newTodo = new Todo;
        $newTodo->description = 'test message';
        $newTodo->user()->associate($testUserA);
        $result = $newTodo->save();

        $this->assertEquals(true, $result);

        $task = $testUserA->todos
            ->find($newTodo->id);

        // Update the task as completed
        $task->toggleStatus();
        $result = $task->save();
        $this->assertEquals(true, $result);

        // check the status is completed
        $task = $testUserA->todos
            ->find($newTodo->id);
        $this->assertEquals(1, $task->status);

        // Delete the task
        $result = $task->delete();
        $this->assertEquals(true, $result);
    }
}
