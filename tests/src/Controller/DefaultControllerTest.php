<?php
namespace Lazer\Classes;

use LazerRest\Api;
use LazerRest\Controller\DefaultController;
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
        unlink(LAZER_DATA_PATH . '/test-model.data.json');
        unlink(LAZER_DATA_PATH . '/test-model.config.json');

        $api = new Api(new App());

        $api->createModel('test-model', [
            'title' => 'string',
            'edited' => 'integer'
        ]);

        $controller = new DefaultController([
            'modelName' => 'test-model'
        ]);

        $controller->setPayload([
            'testModel' => [
                'title' => 'hello'
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
        unlink(LAZER_DATA_PATH . '/test-model.data.json');
        unlink(LAZER_DATA_PATH . '/test-model.config.json');

        $api = new Api(new App());

        $api->createModel('test-model', [
            'title' => 'string',
            'edited' => 'integer'
        ]);

        $controller = new DefaultController([
            'modelName' => 'test-model'
        ]);

        $controller->setPayload([
            'testModel' => [
                'title' => 'test'
            ]
        ]);

        $controller->createAction();

        $this->assertEquals($controller->showAction(1)['testModel']['title'], 'test');

        $controller->setPayload([
            'testModel' => [
                'title' => 'test2'
            ]
        ]);

        $controller->updateAction(1);

        $this->assertEquals($controller->showAction(1)['testModel']['title'], 'test2');

        $controller->deleteAction(1);

        $this->assertEquals(count($controller->indexAction()['testModels']),0);
    }

}