<?php


use Fhp\Rest\Controller\EntityController;
use Fhp\Rest\Test\TestModel;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Stream;
use Slim\Http\Uri;

class EntityControllerTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     */
    public function testControllerIndexAction()
    {
        $controller = new EntityController(TestModel::class, 'testModel');

        $response = json_decode($controller->indexAction(
            $this->createRequest('/testModels'),
            $this->createResponse()
        )->getBody(), true);

        $this->assertArrayHasKey('testModels', $response);
    }

    /**
     *
     */
    public function testAllActions()
    {
        $controller = new EntityController(TestModel::class, 'testModel');
        $controller->setMockedPayload([
            'testModel' => [
                'title' => 'Welcome test',
                'content' => 'Hello world',
                'deleted' => 'null',
            ]
        ]);

        $response = json_decode($controller->createAction(
            $this->createRequest('/testModels', 'POST'),
            $this->createResponse()
        )->getBody(), true);

        $createdId = $response['testModel']['id'];

        $response = json_decode($controller->showAction(
            $this->createRequest('/testModels/' . $createdId, 'POST'),
            $this->createResponse(),
            ['id' => $createdId]
        )->getBody(), true);

        $this->assertEquals($response['testModel']['id'], $createdId);

        $controller->setMockedPayload([
            "testModel" => [
                "title" => "My new title"
            ]
        ]);

        $controller->updateAction(
            $this->createRequest('/testModels/' . $createdId, 'PUT'),
            $this->createResponse(),
            ['id' => $createdId]
        );

        $controller = new EntityController(TestModel::class, 'testModel');

        $response = json_decode($controller->showAction(
            $this->createRequest('/testModels/' . $createdId, 'GET'),
            $this->createResponse(),
            ['id' => $createdId]
        )->getBody(), true);

        $this->assertEquals($response['testModel']['title'], "My new title");

        $response = json_decode($controller->deleteAction(
            $this->createRequest('/testModels/' . $createdId, 'DELETE'),
            $this->createResponse(),
            ['id' => $createdId]
        )->getBody(), true);

        $this->assertContains('deleted', $response['message']);
        $this->assertContains($createdId, $response['message']);

        try {
            $controller->showAction(
                $this->createRequest('/testModels/' . $createdId, 'GET'),
                $this->createResponse(),
                ['id' => $createdId]
            );
        } catch (Exception $e) {
            $exception = $e;
        }

        $this->assertTrue(!empty($exception));
    }

    /**
     * @param $path
     * @param string $method
     * @param null $port
     * @return Request
     */
    public function createRequest($path, $method = 'GET', $port = null)
    {
        return new Request($method, new Uri('http', 'localhost', $port, $path), new Headers(), [], [],
            new Stream(stream_context_create()));
    }

    /**
     * @return Response
     */
    public function createResponse()
    {
        return new Response();
    }

}