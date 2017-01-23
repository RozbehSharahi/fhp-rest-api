<?php

use Lazer\Classes\Helpers\Validate;
use LazerRest\Api;
use Slim\App;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Stream;
use Slim\Http\Uri;

class DatabaseTest extends \PHPUnit_Framework_TestCase
{

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
     *
     */
    public function testCreatingModelOnExistingTable()
    {
        $api = new Api(new App);

        $api->createModel('test-model', [
            'title' => 'string',
            'content' => 'string',
        ]);

        // Do it again here
        $api->createModel('test-model', [
            'title' => 'string',
            'content' => 'string',
        ]);

        try {
            $tableCreated = Validate::table('test-model')->exists();
        } catch (\Exception $e) {
            $tableCreated = false;
        }


        $this->assertEquals($tableCreated, true);
    }

    public function testCreateModelOnNotExitingTable()
    {
        $api = new Api(new App);

        $api->createModel('test-model', [
            'title' => 'string',
            'content' => 'string',
        ]);

        try {
            $tableCreated = Validate::table('test-model')->exists();
        } catch (\Exception $e) {
            $tableCreated = false;
        }

        $this->assertEquals($tableCreated, true);
    }

    public function testHeaders()
    {
        $api = new Api(new App, [
            'headers' => [
                'myAdditionalHeader' => 'hello'
            ]
        ]);

        $api->createModel('test-model', [
            'title' => 'string'
        ]);

        $api->process(
            new Request('GET', new Uri('http', 'host', null, '/testModels'), new Headers(), [], [],
                new Stream($this->getMockStream())),
            new Response()
        );

        $response = $api->run(true);

        $this->assertArrayHasKey('myAdditionalHeader', $response->getHeaders());
        $this->assertArrayHasKey('Content-Type', $response->getHeaders());
    }

    public function testSetOwnHeaders()
    {
        $api = new Api(new App);

        $api->setHeaders([
            'test-header' => 'hello'
        ]);

        $api->createModel('test-model', [
            'title' => 'string'
        ]);

        $api->process(
            new Request('GET', new Uri('http', 'host', null, '/testModels'), new Headers(), [], [],
                new Stream($this->getMockStream())),
            new Response()
        );

        $response = $api->run(true);
        $this->assertArrayHasKey('test-header', $response->getHeaders());
        $this->assertArrayHasKey('test-header', $api->getHeaders());

        $this->assertEquals(true,false);
    }

    /**
     * @return resource
     */
    public function getMockStream()
    {
        $context = stream_context_create(
            array(
                'http' => array(
                    'method' => 'GET'
                )
            )
        );
        $stream = fopen('http://example.com', 'r', false, $context);
        return $stream;
    }

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