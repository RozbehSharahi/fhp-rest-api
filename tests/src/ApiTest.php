<?php

use Fhp\Rest\Database;
use Fhp\Rest\Api;
use Slim\App;
use Slim\Http\Uri;

class DatabaseTest extends \PHPUnit_Framework_TestCase
{
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
     *
     */
    public function testCreatingModelOnExistingTable()
    {
        $api = new Api(new App);

        // Double set model
        $api
            ->createModel('test-model', [
                'title' => 'string',
                'content' => 'string',
            ])->createModel('test-model', [
                'title' => 'string',
                'content' => 'string',
            ]);

        $this->assertEquals($api->getDatabase()->hasTable('test-model'), true);
    }

    public function testCreateModelOnNotExitingTable()
    {
        $api = new Api(new App);

        $api->createModel('test-model', [
            'title' => 'string',
            'content' => 'string',
        ]);

        $this->assertEquals($api->getDatabase()->hasTable('test-model'), true);
    }

    public function testHeaders()
    {
        $api = new Api(new App, [
            'headers' => [
                'Content-Type' => 'test',
                'myAdditionalHeader' => 'hello'
            ]
        ]);

        $api->createModel('test-model', [
            'title' => 'string'
        ]);

        $request = $api->getContainer()->get('request')->withUri(new Uri('http', 'localhost', 80, '/testModels'));
        $response = $api->getContainer()->get('response');

        $response = $api->process($request, $response);

        $this->assertArrayHasKey('myAdditionalHeader', $response->getHeaders());
        $this->assertArrayHasKey('Content-Type', $response->getHeaders());
    }

    public function testSetHeadersBySetter()
    {
        $api = new Api(new App);

        $api->setHeaders([
            'test-header' => 'hello'
        ]);

        $api->createModel('test-model', [
            'title' => 'string'
        ]);

        $request = $api->getContainer()->get('request')->withUri(new Uri('http', 'localhost', 80, '/testModels'));
        $response = $api->getContainer()->get('response');
        $response = $api->process($request, $response);

        $this->assertArrayHasKey('test-header', $response->getHeaders());
        $this->assertArrayNotHasKey('Content-Type', $response->getHeaders());
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