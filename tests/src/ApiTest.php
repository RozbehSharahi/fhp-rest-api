<?php
namespace Lazer\Classes;

use Lazer\Classes\Helpers\Validate;
use LazerRest\Api;
use Slim\App;

class DatabaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * @var App
     */
    protected $app;

    /**
     *
     */
    public function setUp()
    {
        $this->app = new App();
        $this->api = new Api($this->app);
    }

    /**
     *
     */
    public function testCreatingModel()
    {
        $this->api->createModel('test-model', [
            'title' => 'string',
            'content' => 'string',
        ]);

        $tableCreated = false;
        try {
            $tableCreated = Validate::table('test-model')->exists();
        } catch (\Exception $e) {

        }

        $this->assertEquals($tableCreated, true);
    }

    public function testCreateModelOnNotExitingTable()
    {
        unlink(LAZER_DATA_PATH.'/test-model.config.json');
        unlink(LAZER_DATA_PATH.'/test-model.data.json');

        $this->api->createModel('test-model', [
            'title' => 'string',
            'content' => 'string',
        ]);

        $tableCreated = false;
        try {
            $tableCreated = Validate::table('test-model')->exists();
        } catch (\Exception $e) {

        }

        $this->assertEquals($tableCreated, true);
    }
}