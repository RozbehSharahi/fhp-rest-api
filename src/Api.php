<?php

namespace LazerRest;

use ICanBoogie\Inflector;
use Lazer\Classes\Database;
use Lazer\Classes\Helpers\Validate;
use LazerRest\Controller\DefaultController;
use Slim\App;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class LazerRest\Api
 *
 * @package LazerRest
 */
class Api
{
    /**
     * @var App
     */
    protected $app;

    /**
     * @var Inflector
     */
    protected $inflector;

    /**
     * Api constructor.
     *
     * @param App $app
     * @throws \Exception
     */
    public function __construct($app)
    {
        // Create database directory if needed
        if (defined('LAZER_DATA_PATH')) {
            if (!is_dir(LAZER_DATA_PATH) && !is_file(LAZER_DATA_PATH)) {
                mkdir(LAZER_DATA_PATH);
            }
        } else {
            //@codeCoverageIgnoreStart
            throw new \Exception('LAZER_DATA_PATH is not defined');
            //@codeCoverageIgnoreEnd
        }

        $this->app = $app;
        $this->inflector = Inflector::get();
    }

    /**
     * Create a route + lazer table ...
     *
     * @param string $modelName
     * @param array $modelConfig
     * @param string $controllerName
     */
    public function createModel($modelName, $modelConfig, $controllerName = 'LazerRest\\Controller\\DefaultController')
    {
        $modelName = $this->inflector->hyphenate($modelName);

        // Create or replace table
        $this->createOrReplaceTable($modelName, $modelConfig);

        // Create route
        $this->createRoutes($modelName, $modelConfig, $controllerName);
    }

    /**
     * Create or replace a Lazer table
     *
     * @param $modelName
     * @param $config
     */
    public function createOrReplaceTable($modelName, $config)
    {
        try {
            Validate::table($modelName)->exists();
        } catch (\Exception $e) {
            Database::create($modelName, $config);
        }
    }

    /**
     * @param string $modelName
     * @param array $modelConfig
     * @param string $controllerName
     */
    public function createRoutes($modelName, $modelConfig, $controllerName)
    {
        $modelPath = '/' . $this->inflector->pluralize(lcfirst($this->inflector->camelize($modelName)));

        // The index path
        $this->app->get($modelPath,
            function (Request $request, Response $response) use ($modelName, $modelConfig, $controllerName) {

                /** @var DefaultController $controller */
                $controller = new $controllerName([
                    'modelName' => $modelName,
                    'modelConfig' => $modelConfig
                ]);

                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withJson($controller->indexAction(), null, JSON_PRETTY_PRINT);
            });

        // The single path
        $this->app->get($modelPath . '/{id}',
            function (Request $request, Response $response, $args) use ($modelName, $modelConfig, $controllerName) {

                /** @var DefaultController $controller */
                $controller = new $controllerName([
                    'modelName' => $modelName,
                    'modelConfig' => $modelConfig
                ]);

                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withJson($controller->showAction($args['id']), null, JSON_PRETTY_PRINT);
            });

        // The update path
        $this->app->put($modelPath . '/{id}',
            function (Request $request, Response $response, $args) use ($modelName, $modelConfig, $controllerName) {

                /** @var DefaultController $controller */
                $controller = new $controllerName([
                    'modelName' => $modelName,
                    'modelConfig' => $modelConfig
                ]);

                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withJson($controller->updateAction($args['id']), null, JSON_PRETTY_PRINT);
            });

        // The create path
        $this->app->post($modelPath,
            function (Request $request, Response $response) use ($modelName, $modelConfig, $controllerName) {

                /** @var DefaultController $controller */
                $controller = new $controllerName([
                    'modelName' => $modelName,
                    'modelConfig' => $modelConfig
                ]);

                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withJson($controller->createAction(), null, JSON_PRETTY_PRINT);
            });

        // The delete path
        $this->app->delete($modelPath . '/{id}',
            function (Request $request, Response $response, $args) use ($modelName, $modelConfig, $controllerName) {

                /** @var DefaultController $controller */
                $controller = new $controllerName([
                    'modelName' => $modelName,
                    'modelConfig' => $modelConfig
                ]);

                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withJson($controller->deleteAction($args['id']), null, JSON_PRETTY_PRINT);
            });
    }

    /**
     * Just start $slim->run :)
     *
     * @codeCoverageIgnore
     */
    public function run()
    {
        $c = $this->app->getContainer();

        $c['errorHandler'] = function ($c) {
            return function ($request, $response, $exception) use ($c) {
                return $c['response']->withStatus(500)
                    ->withHeader('Content-Type', 'application/json')
                    ->withJson(["type" => "error", "message" => $exception->getMessage()], null, JSON_PRETTY_PRINT);
            };
        };

        $this->app->run();
    }

}