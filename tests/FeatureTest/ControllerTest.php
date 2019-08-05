<?php

namespace Tests\FeatureTest;

use Doctrine\ORM\EntityManager;
use Tests\BaseTest;

class ControllerTest extends BaseTest
{
    /**
     * Test the dashboard page
     */
    public function testDashBoard()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertEquals(1, $crawler->filter('h1:contains("README")')->count());
        $this->assertEquals(1, $crawler->filter('.well:contains("AskNicely")')->count());
    }

    /**
     * Test the login action
     * This will direct fetch data from mysql, because the entity manage is forbidden to mock
     */
    public function testLogin()
    {
        $client = static::createClient();


        /** Case-1 if we didn't post the login info, we get the form to login */
        $crawler = $client->request("GET", "/login");
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertEquals(1, $crawler->filter('h1:contains("Login")')->count());

        /** Case-2 if we pass only username param, we will been render to the login page */
        $crawler = $client->request("POST", "/login", array("username" => "user1"));
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertEquals(1, $crawler->filter('h1:contains("Login")')->count());

        /** Case-3 if we pass an error match of the username and password, we will be render to the login page */
        $crawler = $client->request("POST", "/login", array("username" => "user1", "password" => "errorcode"));
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertEquals(1, $crawler->filter('h1:contains("Login")')->count());

        /** Case-4 we login success */
        $crawler = $client->request("POST", "/login", array("username" => "user1", "password" => "user1"));
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('html title:contains("Redirecting to /todos")')->count());
    }

    /**
     * Test the logout function
     */
    public function testLogout()
    {
        $client = static::createClient();
        $s = $this->getMock("Symfony\Component\HttpFoundation\Session\Session");
        $s->method("set")->withConsecutive(array("user", null))->willReturn(true);
        $this->app["session"] = $s;
        $crawler = $client->request("GET", "/logout");
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('html title:contains("Redirecting to /")')->count());
    }

    /**
     * Test the todos list page
     */
    public function testToDos()
    {
        $client = static::createClient();
        $crawler = $client->request("GET", "/todos");

        /** Case-1 unlogin user visit this page **/
        //if we didn't login, we will redirect to the login page
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('html title:contains("Redirecting to /login")')->count());

        /**  Case-2 a user with to-dos more than one page visit this page  **/
        $this->mockUser(1, $this->mockFlashBag(array()));

        $crawler = $client->request("GET", "/todos");
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertEquals(1, $crawler->filter("h1:contains('Todo List')")->count());
        $this->assertGreaterThan(1, $crawler->filter(".pagerfanta li")->count());
        $this->assertGreaterThan(1, $crawler->filter(".table tr")->count());
        $this->assertContains("1", $crawler->filter(".pagerfanta .active span")->html());

        //we now get the page 2 of this user
        $crawler = $client->request("GET", "/todos", array("page" => 2));
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertEquals(1, $crawler->filter("h1:contains('Todo List')")->count());
        $this->assertGreaterThan(1, $crawler->filter(".pagerfanta li")->count());
        $this->assertGreaterThan(1, $crawler->filter(".table tr")->count());
        $this->assertContains("2", $crawler->filter(".pagerfanta .active span")->html());
        $this->assertEquals("/todos", $crawler->filter(".pagerfanta .active")->previousAll()->last()->filter("a")->attr("href"));

        /** Case-3 a user with the numbers of to-dos less than a page capability**/
        $this->mockUser(2, $this->mockFlashBag(array()));
        $crawler = $client->request("GET", "/todos");
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertEquals(4, $crawler->filter(".table tr")->count());
        $this->assertEquals(3, $crawler->filter(".pagerfanta li")->count());

        /** Case-4 a user with no to-dos */
        $this->mockUser(3, $this->mockFlashBag(array()));
        $crawler = $client->request("GET", "/todos");
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertEquals(1, $crawler->filter("h1:contains('Todo List')")->count());
        $this->assertEquals(1, $crawler->filter(".table tr")->count());
        $this->assertEquals(2, $crawler->filter(".pagerfanta .disabled")->count());

        /** Case-5 We visit this page with a invalid page param */
        $warnString = "Wrong Page";
        $this->mockUser(1, $this->mockFlashBag(array("warning", array($warnString))));
        $crawler = $client->request("GET", "/todos", array("page" => "asd"));
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $crawler = $client->followRedirect();
        $this->assertContains($warnString, $crawler->filter(".alert")->html());

        /** Case-6 We visit a page greater than the total page */
        $this->mockUser(1, $this->mockFlashBag(array()));
        $crawler = $client->request("GET", "/todos", array("page" => 10000));
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $crawler = $client->followRedirect();
        $this->assertEquals(1, $crawler->filter("h1:contains('Todo List')")->count());
    }

    /**
     * Test the to do page
     */
    public function testTodo()
    {
        $client = static::createClient();

        /** Case-1 unlogin user visit this page **/
        //if we didn't login, we will redirect to the login page
        $crawler = $client->request("GET", "/todo/1");
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('html title:contains("Redirecting to /login")')->count());

        /** Case-2 we post a invalid todo's id */
        $warnString = "Wrong page";
        $this->mockUser(1, $this->mockFlashBag(array("warning", array($warnString))));
        $client->request("GET", "/todo/asd");
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $crawler = $client->followRedirect();
        $this->assertContains($warnString, $crawler->filter(".alert")->html());
        $this->assertEquals(1, $crawler->filter("h1:contains('Todo List')")->count());

        /** Case-3 we post a todo's id which didn't belong to the login user or not exists in the databse */
        $client->request("GET", "/todo/999");
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $crawler = $client->followRedirect();
        $this->assertEquals(1, $crawler->filter("h1:contains('Todo List')")->count());

        /** Case-4 we post a valid todo's id */
        $crawler = $client->request("GET", "/todo/1");
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertEquals(1, $crawler->filter(".table tr")->count());
        $this->assertEquals(1, $crawler->filter(".table td")->nextAll()->first()->html());


    }

    /**
     * Test the to do json page
     */
    public function testTodoJson()
    {
        $client = static::createClient();

        /** Case-1 unlogin user visit this page **/
        //if we didn't login, we will redirect to the login page
        $crawler = $client->request("GET", "/todo/1/json");
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('html title:contains("Redirecting to /login")')->count());

        /** Case-2 we post a invalid todo's id */
        $this->mockUser(1, $this->mockFlashBag(array()));
        $client->request("GET", "/todo/asd/json");
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $crawler = $client->followRedirect();
        $this->assertEquals(1, $crawler->filter("h1:contains('Todo List')")->count());

        /** Case-3 we post a todo's id which didn't belong to the login user or not exists in the databse */
        $client->request("GET", "/todo/999");
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $crawler = $client->followRedirect();
        $this->assertEquals(1, $crawler->filter("h1:contains('Todo List')")->count());

        /** Case-4 we post a valid todo's id */
        $crawler = $client->request("GET", "/todo/1/json");
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertEquals(1, $crawler->filter(".table tr")->count());
        $jsonString = $crawler->filter(".table td")->nextAll()->first()->html();
        $jsonObject = json_decode($jsonString, true);
        $this->assertEquals(0, json_last_error());
        $this->assertEquals(1, $jsonObject["id"]);
    }

    /**
     * Test the add function
     */
    public function testAdd()
    {
        $client = static::createClient();

        /** Case-1 unlogin user visit this page **/
        //if we didn't login, we will redirect to the login page
        $crawler = $client->request("POST", "/todo/add", array("description" => "asdsd"));
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('html title:contains("Redirecting to /login")')->count());

        /** Case-2 we post an empty description */
        $warnString = "We Got an error";
        $this->mockUser(1, $this->mockFlashBag(array("danger", array($warnString))));
        $client->request("POST", "/todo/add");
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $crawler = $client->followRedirect();
        $this->assertContains($warnString, $crawler->filter(".alert")->html());
        $this->assertEquals(1, $crawler->filter("h1:contains('Todo List')")->count());

        /** Case-3 we post an description langer than 255 */
        $warnString = "Too Long";
        $this->mockUser(1, $this->mockFlashBag(array("danger", array($warnString))));
        $client->request("POST", "/todo/add", array("description" => str_repeat("1", 256)));
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $crawler = $client->followRedirect();
        $this->assertContains($warnString, $crawler->filter(".alert")->html());
        $this->assertEquals(1, $crawler->filter("h1:contains('Todo List')")->count());

        /** Case-4 the normal add  */
        $desc = str_repeat("1", 255);
        $this->mockUser(1, $this->mockFlashBag("info", array("Success")));
        $client->request("POST", "/todo/add", array("description" => $desc));
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertEquals(1, $crawler->filter("h1:contains('Todo')")->count());
        $id = $crawler->filter(".table td a")->first()->html();
        $desctiption = $crawler->filter(".table td")->last()->previousAll()->first()->html();
        $this->assertEquals($desc, $desctiption);
        $o = $this->app["db.orm.em"]->getRepository("Entity\Todo")->find($id);
        $this->app["db.orm.em"]->remove($o);
        $this->app["db.orm.em"]->flush();
    }

    /**
     * Test the delete function
     */
    public function testDeleteTodo()
    {
        $client = static::createClient();

        /** Case-1 unlogin user visit this page **/
        //if we didn't login, we will redirect to the login page
        $crawler = $client->request("POST", "/todo/delete/1");
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('html title:contains("Redirecting to /login")')->count());

        /** Case-2 delete a to-do not belong to the login user */
        $this->mockUser(2, $this->mockFlashBag(array()));
        $client->request("POST", "/todo/delete/1");
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $crawler = $client->followRedirect();
        $this->assertEquals(1, $crawler->filter("h1:contains('Todo List')")->count());
        $t = $this->app["db.orm.em"]->getRepository("Entity\Todo")->find(1);
        $this->assertEquals(1, $t->getId());

        /** Case-3 delete a to-do */
        $desc = "test for delete";
        $this->mockUser(1, $this->mockFlashBag(array("info" => array("success"))));
        $client->request("POST", "/todo/add", array("description" => $desc));
        $crawler = $client->followRedirect();
        $id = $crawler->filter(".table td a")->first()->html();
        $this->assertEquals($desc, $crawler->filter(".table td")->last()->previousAll()->first()->html());
        $this->mockUser(1, $this->mockFlashBag(array("info" => array("delete success"))));
        $client->request("POST", "/todo/delete/" . $id);
        $crawler = $client->followRedirect();
        $this->assertContains("delete success", $crawler->filter(".alert")->html());
        $this->assertEquals(1, $crawler->filter("h1:contains('Todo List')")->count());
        $this->assertEquals(null, $this->app["db.orm.em"]->getRepository("Entity\Todo")->find($id));
    }


    /**
     * Test mark to-do done
     */
    function testDoneToDo()
    {
        $client = static::createClient();

        /** Case-1 unlogin user visit this page **/
        //if we didn't login, we will redirect to the login page
        $crawler = $client->request("POST", "/todo/done/1");
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('html title:contains("Redirecting to /login")')->count());

        /** Case-2 mark a to-do not belong to the login user */
        $this->mockUser(2, $this->mockFlashBag(array()));
        $client->request("POST", "/todo/done/1");
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $crawler = $client->followRedirect();
        $this->assertEquals(1, $crawler->filter("h1:contains('Todo List')")->count());
        $t = $this->app["db.orm.em"]->getRepository("Entity\Todo")->find(1);
        $this->assertFalse($t->hasDone());

        /** Case-3 mark a to-do is Done  */
        $infoString = "test for mark done";
        $deleteString = "delete success";
        $this->mockUser(1, $this->mockFlashBag(array("info" => array("success"))));
        $client->request("POST", "/todo/add", array("description" => $infoString));
        $crawler = $client->followRedirect();
        $id = $crawler->filter(".table td a")->first()->html();
        $this->assertEquals($infoString, $crawler->filter(".table td")->last()->previousAll()->first()->html());
        $this->assertFalse($this->app["db.orm.em"]->getRepository("Entity\Todo")->find($id)->hasDone());
        $this->mockUser(1, $this->mockFlashBag(array()));
        $client->request("POST", "/todo/done/" . $id);
        $crawler = $client->followRedirect();
        $this->assertEquals(1, $crawler->filter("h1:contains('Todo List')")->count());
        $this->assertTrue($this->app["db.orm.em"]->getRepository("Entity\Todo")->find($id)->hasDone());
        $this->mockUser(1, $this->mockFlashBag(array("info" => array($deleteString))));
        $client->request("POST", "/todo/delete/" . $id);
        $crawler = $client->followRedirect();
        $this->assertContains($deleteString, $crawler->filter(".alert")->html());
        $this->assertEquals(1, $crawler->filter("h1:contains('Todo List')")->count());
        $this->assertEquals(null, $this->app["db.orm.em"]->getRepository("Entity\Todo")->find($id));
    }

    /**
     * Mock different user with uid
     * @param $userId
     */
    private function mockUser($userId, $fb)
    {
        $u = $this->getMock("Entity\User");
        $u->method("getId")->willReturn($userId);
        //then we mock the session object
        $s = $this->getMock("Symfony\Component\HttpFoundation\Session\Session");
        $s->method("get")->withConsecutive(array("user"))->willReturn($u);
        $s->method("getFlashBag")->willReturn($fb);
        //then we replace the session object in the container
        $this->app["session"] = $s;
    }

    /**
     * Mock a flash bag
     *
     * @param $flash
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockFlashBag($flash)
    {
        //first we mock a user and a flash bag
        $fb = $this->getMock("Symfony\Component\HttpFoundation\Session\Flash\FlashBag");
        if ($flash) {
            $fb->expects($this->once())->method("add")->willReturn("true");
        }
        $fb->method("all")->willReturn($flash);
        return $fb;
    }

}