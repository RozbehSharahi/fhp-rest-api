<?php

namespace Fhp\Rest;

use ICanBoogie\Inflector;
use Slim\App;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Fhp\Rest\Api
 *
 * Api class is a wrapper for slim. Instead of defining routes
 * you define models. Routes will be generated automatically.
 *
 * It also handles the creation of Lazer-Databases for your models.
 *
 * Feel free to extend from this class and change functions.
 *
 * @package Fhp\Rest
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
     * @var Database
     */
    protected $database;

    /**
     * Default headers that may be overwritten
     *
     * @var array
     */
    protected $headers = [
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
        $this->headers = !empty($config['headers']) ? $config['headers'] : $this->headers;
        $this->database = new Database();

        // Create database directory
        $this->fileManager->createDirectory(LAZER_DATA_PATH);
    }

    /**
     * Create a route + lazer table ...
     *
     * @param string $modelName
     * @param array $modelConfig
     * @param string $controllerName
     * @return $this
     */
    public function createModel($modelName, $modelConfig, $controllerName = Controller::class)
    {
        $modelName = $this->inflector->hyphenate($modelName);
        return $this
            ->createTable($modelName, $modelConfig)
            ->createRoutes($modelName, $controllerName);
    }

    /**
     * Create or replace a Lazer table
     *
     * @param string $modelName
     * @param array $modelConfig
     * @return $this
     */
    public function createTable($modelName, $modelConfig)
    {
        if (!$this->database->hasTable($modelName)) {
            $this->database->createTable($modelName, $modelConfig);
        }
        return $this;
    }

    /**
     * Assign headers to app
     *
     * @return $this
     */
    protected function assignHeaders()
    {
        $headers = $this->headers;
        $this->app->add(function (Request $request, Response $response, $next) use ($headers) {
            /** @var Response $response */
            $response = $next($request, $response);
            foreach ($headers as $headerName => $headerValue) {
                $response = $response->withHeader($headerName, $headerValue);
            }
            return $response;
        });
        return $this;
    }

    /**
     * Assign error handling to app
     *
     * @return $this
     */
    protected function assignErrorHandling()
    {
        $this->app->getContainer()['errorHandler'] = function ($c) {
            return function () use ($c) {
                /** @var \Exception $exception */
                $exception = func_get_arg(2);
                /** @var Response $response */
                $response = $c['response'];
                return $response->withStatus(500)
                    ->withHeader('Content-Type', 'application/json')
                    ->withJson(["type" => "error", "message" => $exception->getMessage()], null, JSON_PRETTY_PRINT);
            };
        };
        return $this;
    }

    /**
     * @param string $modelName
     * @param string $controllerName
     * @return $this
     */
    public function createRoutes($modelName, $controllerName = Controller::class)
    {
        $controller = new $controllerName(['modelName' => $modelName]);

        return $this
            ->assignHeaders()
            ->assignErrorHandling()
            ->createIndexRoute($modelName, $controller)
            ->createShowRoute($modelName, $controller)
            ->createUpdateRoute($modelName, $controller)
            ->createCreateRoute($modelName, $controller)
            ->createDeleteRoute($modelName, $controller);
    }

    /**
     * /modelName (GET)
     *
     * @param $modelName
     * @param $controller
     * @return $this
     */
    public function createIndexRoute($modelName, $controller)
    {
        $modelPath = '/' . $this->inflector->pluralize(lcfirst($this->inflector->camelize($modelName)));
        $this->app->get($modelPath, [$controller, 'indexAction']);
        return $this;
    }

    /**
     * /modelName/id (GET)
     *
     * @param $modelName
     * @param $controller
     * @return $this
     */
    public function createShowRoute($modelName, $controller)
    {
        $modelPath = '/' . $this->inflector->pluralize(lcfirst($this->inflector->camelize($modelName)));
        $this->app->get($modelPath . '/{id}', [$controller, 'showAction']);
        return $this;
    }

    /**
     * /modelName/id (PUT)
     *
     * @param $modelName
     * @param $controller
     * @return $this
     */
    public function createUpdateRoute($modelName, $controller)
    {
        $modelPath = '/' . $this->inflector->pluralize(lcfirst($this->inflector->camelize($modelName)));
        $this->app->put($modelPath . '/{id}', [$controller, 'updateAction']);
        return $this;
    }

    /**
     * /modelName (POST)
     *
     * @param $modelName
     * @param $controller
     * @return $this
     */
    public function createCreateRoute($modelName, $controller)
    {
        $modelPath = '/' . $this->inflector->pluralize(lcfirst($this->inflector->camelize($modelName)));
        $this->app->post($modelPath, [$controller, 'createAction']);
        return $this;
    }

    /**
     * /modelName/id (DELETE)
     *
     * @param $modelName
     * @param $controller
     * @return $this
     */
    public function createDeleteRoute($modelName, $controller)
    {
        $modelPath = '/' . $this->inflector->pluralize(lcfirst($this->inflector->camelize($modelName)));
        $this->app->delete($modelPath . '/{id}', [$controller, 'deleteAction']);
        return $this;
    }

    /**
     * Forward process to app
     *
     * @param Request $request
     * @param Response $response
     * @return Response $response
     */
    public function process(Request $request, Response $response)
    {
        return $this->app->process($request, $response);
    }

    /**
     * Forward run to app
     *
     * @param boolean $silent
     * @return Response|void
     */
    public function run($silent = false)
    {
        return $this->app->run($silent);
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     * @return $this
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @return Database
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @param Database $database
     * @return $this
     */
    public function setDatabase($database)
    {
        $this->database = $database;
        return $this;
    }

    /**
     * @return App
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @param App $app
     * @return $this
     */
    public function setApp($app)
    {
        $this->app = $app;
        return $this;
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->app->getContainer();
    }
}