<?php

namespace LazerRest;

use ICanBoogie\Inflector;
use Lazer\Classes\Database;
use Lazer\Classes\Helpers\Validate;
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
     * @var FileManager
     */
    protected $fileManager;

    /**
     * Default headers that may be overwritten
     *
     * @var array
     */
    protected $defaultHeaders = [
        'Content-Type' => 'application/json',
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Headers' => 'X-Requested-With, Content-Type, Accept, Origin, Authorization',
        'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS'
    ];

    /**
     * Api constructor.
     *
     * @param App $app
     * @param array $config
     * @throws \Exception
     */
    public function __construct($app, $config = null)
    {
        $this->app = $app;
        $this->inflector = Inflector::get();
        $this->fileManager = new FileManager();

        // Merge configured headers with defaultHeaders
        $this->defaultHeaders = !empty($config['defaultHeaders']) ? array_replace_recursive($this->defaultHeaders,
            $config['defaultHeaders']) : $this->defaultHeaders;

        // Create database directory
        $this->fileManager->createDirectory(LAZER_DATA_PATH);
    }

    /**
     * Create a route + lazer table ...
     *
     * @param string $modelName
     * @param array $modelConfig
     * @param string $controllerName
     */
    public function createModel($modelName, $modelConfig, $controllerName = DefaultController::class)
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

        // Default configs for response
        $this->setDefaultHeaders();

        // The index path
        $this->app->get($modelPath,
            function (Request $request, Response $response) use ($modelName, $modelConfig, $controllerName) {

                /** @var DefaultController $controller */
                $controller = new $controllerName([
                    'modelName' => $modelName,
                    'modelConfig' => $modelConfig
                ]);

                return $response
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
                    ->withJson($controller->deleteAction($args['id']), null, JSON_PRETTY_PRINT);
            });
    }

    /**
     * Set default headers for response
     *
     * return void
     */
    public function setDefaultHeaders()
    {
        $defaultHeaders = $this->defaultHeaders;

        $this->app->add(function (Request $request, Response $response, $next) use ($defaultHeaders) {

            /** @var Response $response */
            $response = $next($request, $response);

            // Set headers
            foreach ($defaultHeaders as $headerName => $headerValue) {
                $response = $response->withHeader($headerName, $headerValue);
            }

            // Return final response
            return $response;
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
            return function (Request $request, Response $response,  $exception) use ($c) {
                return $c['response']->withStatus(500)
                    ->withHeader('Content-Type', 'application/json')
                    ->withJson(["type" => "error", "message" => $exception->getMessage()], null, JSON_PRETTY_PRINT);
            };
        };

        $this->app->run();
    }
}