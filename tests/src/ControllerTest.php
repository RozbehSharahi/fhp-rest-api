<?php

use Fhp\Rest\Api;
use Fhp\Rest\Controller;
use Fhp\Rest\Database;
use Slim\App;

class ControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Controller
     */
    protected $controller;

    /**
     *
     */
    public function setUp()
    {
        if (is_file(LAZER_DATA_PATH . '/test-model.data.json')) {
            unlink(LAZER_DATA_PATH . '/test-model.data.json');
        }
        if (is_file(LAZER_DATA_PATH . '/test-model.config.json')) {
            unlink(LAZER_DATA_PATH . '/test-model.config.json');
        }
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
        $api = new Api(new App());

        $api->createModel('test-model', [
            'title' => 'string'
        ]);

        $controller = new Controller([
            'modelName' => 'test-model'
        ]);

        $this->assertTrue(is_array($controller->indexAction()));
    }

    /**
     *
     */
    public function testShowAction()
    {

        $api = new Api(new App());

        $api->createModel('test-model', [
            'title' => 'string',
            'edited' => 'integer'
        ]);

        $controller = new Controller([
            'modelName' => 'test-model',
            'payload' => [
                'testModel' => [
                    'title' => 'hello'
                ]
            ]
        ]);

        $controller->createAction();

        $this->assertEquals(count($controller->indexAction()), 1);

        $this->assertEquals($controller->showAction(1)['testModel']['title'], 'hello');
    }

    /**
     *
     */
    public function testUpdateAction()
    {

        $api = new Api(new App());

        $api->createModel('test-model', [
            'title' => 'string',
            'edited' => 'integer'
        ]);

        $controller = new Controller([
            'modelName' => 'test-model',
            'payload' => [
                'testModel' => [
                    'title' => 'test'
                ]
            ]
        ]);

        $controller->createAction();

        $this->assertEquals($controller->showAction(1)['testModel']['title'], 'test');

        $controller = new Controller([
            'modelName' => 'test-model',
            'payload' => [
                'testModel' => [
                    'title' => 'test2'
                ]
            ]
        ]);

        $controller->updateAction(1);

        $this->assertEquals($controller->showAction(1)['testModel']['title'], 'test2');

        $controller->deleteAction(1);

        $this->assertEquals(count($controller->indexAction()['testModels']), 0);
    }

    /**
     * @expectedException Exception
     */
    public function testInvalidPayload()
    {
        Database::create('test-model', [
            'title' => 'string'
        ]);

        $controller = new Controller([
            'modelName' => 'test-model',
            'payload' => '"NO}Valid JSOn"$§'
        ]);

        try {
            $controller->createAction();
        } catch (\Exception $e) {
            throw new Exception('Error');
        }
    }

    /**
     * Teardown the database
     */
    public function tearDown()
    {
        if (is_file(LAZER_DATA_PATH . '/test-model.data.json')) {
            unlink(LAZER_DATA_PATH . '/test-model.data.json');
        }
        if (is_file(LAZER_DATA_PATH . '/test-model.config.json')) {
            unlink(LAZER_DATA_PATH . '/test-model.config.json');
        }
    }

}