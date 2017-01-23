<?php

use LazerRest\Api;
use LazerRest\DefaultController;
use Slim\App;

class DefaultControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DefaultController
     */
    protected $controller;

    /**
     *
     */
    public function setUp()
    {
        if(is_file(LAZER_DATA_PATH . '/test-model.data.json')) {
            unlink(LAZER_DATA_PATH . '/test-model.data.json');
        }
        if(is_file(LAZER_DATA_PATH . '/test-model.config.json')) {
            unlink(LAZER_DATA_PATH . '/test-model.config.json');
        }
    }

    /**
     * @expectedException \Exception
     */
    public function testControllerMustHaveModelName()
    {
        new DefaultController();
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

        $controller = new DefaultController([
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

        $controller = new DefaultController([
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

        $controller = new DefaultController([
            'modelName' => 'test-model',
            'payload' => [
                'testModel' => [
                    'title' => 'test'
                ]
            ]
        ]);

        $controller->createAction();

        $this->assertEquals($controller->showAction(1)['testModel']['title'], 'test');

        $controller = new DefaultController([
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
     * Teardown the database
     */
    public function tearDown()
    {
        if(is_file(LAZER_DATA_PATH . '/test-model.data.json')) {
            unlink(LAZER_DATA_PATH . '/test-model.data.json');
        }
        if(is_file(LAZER_DATA_PATH . '/test-model.config.json')) {
            unlink(LAZER_DATA_PATH . '/test-model.config.json');
        }
    }

}