<?php

use Lazer\Classes\Helpers\Validate;
use LazerRest\Api;
use Slim\App;

class DatabaseTest extends \PHPUnit_Framework_TestCase
{

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