<?php

use Fhp\Rest\Controller;
use Fhp\Rest\Database;
use Psr\Http\Message\ResponseInterface;

class ControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Controller
     */
    protected $controller;

    /**
     * @var Database
     */
    protected $database;

    /**
     *
     */
    public function setUp()
    {
        $this->database = new Database();
        $this->database->createTable('test-model', [
            'title' => 'string',
            'content' => 'string',
        ]);
    }

    /**
     * @expectedException \Exception
     */
    public function testControllerMustHaveModelName()
    {
        new Controller([
            'database' => 'FAKE DB'
        ]);
    }

    /**
     *
     */
    public function testIndexAction()
    {
        $controller = new Controller([
            'modelName' => 'test-model'
        ]);
        $response = $this->action($controller, 'indexAction');
        $this->assertTrue(is_array($response));
    }

    /**
     *
     */
    public function testShowAction()
    {
        $controller = new Controller([
            'modelName' => 'test-model',
        ]);

        $controller->setMockedPayload([
            'testModel' => [
                'title' => 'hello'
            ]
        ]);

        $controller->createAction();

        $response = $this->action($controller, 'indexAction');
        $this->assertEquals(count($response['testModels']), 1);

        $response = $this->action($controller, 'showAction', 1);
        $this->assertEquals($response['testModel']['title'], 'hello');
    }

    /**
     *
     */
    public function testUpdateAction()
    {
        $controller = new Controller([
            'modelName' => 'test-model'
        ]);
        $controller->setMockedPayload([
            'testModel' => [
                'title' => 'test'
            ]
        ]);

        $response = $this->action($controller, 'createAction');
        $this->assertEquals($response['testModel']['title'], 'test');

        $controller->setMockedPayload([
            'testModel' => [
                'title' => 'test2'
            ]
        ]);

        $response = $this->action($controller, 'updateAction', 1);
        $this->assertEquals($response['testModel']['title'], 'test2');

        $this->action($controller, 'deleteAction', 1);
        $response = $this->action($controller, 'indexAction');
        $this->assertEquals(count($response['testModels']), 0);
    }

    /**
     * @expectedException Exception
     */
    public function testInvalidPayload()
    {
        $controller = new Controller([
            'modelName' => 'test-model',
        ]);
        $controller->setMockedPayload(['payload' => '"NO}Valid JSOn"$§']);

        try {
            $this->action($controller, 'createAction');
        } catch (\Exception $e) {
            throw new Exception('Error');
        }
    }

    protected function toArray(ResponseInterface $response)
    {
        return json_decode($response->getBody()->__toString(), true);
    }

    /**
     * @param Controller $controller
     * @param string $action
     * @param array $parameters
     * @return array
     */
    protected function action($controller, $action, $parameters = null)
    {
        if (!is_array($parameters)) {
            /** @var array $parameters default parameter types for an action */
            $parameters = [null, null, $parameters];
        }
        return $this->toArray(call_user_func_array([$controller, $action], $parameters));
    }

    /**
     * Teardown the database
     */
    public function tearDown()
    {
        $this->database->remove('test-model');
        $this->database->remove('system');
    }

}