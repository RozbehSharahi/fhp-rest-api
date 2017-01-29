<?php

use Fhp\Rest\Api;
use Fhp\Rest\Controller\EntityController;
use Fhp\Rest\Controller\FlexEntityController;
use Fhp\Rest\Test\TestModel;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Stream;
use Slim\Http\Uri;

class DatabaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Api
     */
    protected $api;

    /**
     *
     */
    public function setUp()
    {

    }

    public function testGetters()
    {
        $api = Api::create();
        $this->assertTrue(is_object($api->getApp()));
        $this->assertTrue(is_object($api->getContainer()));
        $this->assertTrue(is_array($api->getHeaders()));
    }

    /**
     *
     */
    public function testActivateEntity()
    {
        $api = Api::create()
            ->activateEntity(TestModel::class);

        // Index
        $response = json_decode($api->process(
            $this->createRequest('/testModels'),
            $this->createResponse()
        )->getBody(), true);

        $this->assertArrayHasKey('testModels', $response);

        $response = json_decode($api->process(
            $this->createRequest('/testModels/NOT_EXISTING_ID'),
            $this->createResponse()
        )->getBody(), true);

        $this->assertEquals($response['type'], 'error');
    }

    /**
     *
     */
    public function testCustomNodeForEntity()
    {
        $api = Api::create()
            ->activateEntity(TestModel::class, EntityController::class, 'myCustomNode');

        $response = $api->process(
            $this->createRequest('/testModels'),
            $this->createResponse()
        );

        $this->assertTrue($response->getStatusCode() == 404);

        $response = $api->process(
            $this->createRequest('/myCustomNodes'),
            $this->createResponse()
        );

        $this->assertTrue($response->getStatusCode() == 200);
    }

    /**
     *
     */
    public function testActivateFlexEntity()
    {
        $api = Api::create()
            ->activateEntity('testModel', FlexEntityController::class);

        // Index
        $response = json_decode($api->process(
            $this->createRequest('/testModels'),
            $this->createResponse()
        )->getBody(), true);

        $this->assertArrayHasKey('testModels', $response);

        $response = json_decode($api->process(
            $this->createRequest('/testModels/NOT_EXISTING_ID'),
            $this->createResponse()
        )->getBody(), true);

        $this->assertEquals($response['type'], 'error');
    }

    /**
     *
     */
    public function testSetHeaders()
    {
        $api = Api::create()
            ->setHeaders(['test-header' => 'hello'])
            ->activateEntity(TestModel::class);

        $response = $api->process(
            $this->createRequest('/testModels'),
            $this->createResponse()
        );

        $this->assertArrayHasKey('test-header', $response->getHeaders());
        $this->assertArrayHasKey('Content-Type', $response->getHeaders());
        $this->assertArrayNotHasKey('Access-Control-Allow-Methods', $response->getHeaders());
    }

    /**
     *
     */
    public function testDefaultHeaders()
    {
        $api = Api::create()
            ->activateEntity(TestModel::class);

        $response = $api->process(
            $this->createRequest('/testModels'),
            $this->createResponse()
        );

        $this->assertArrayHasKey('Access-Control-Allow-Methods', $response->getHeaders());
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