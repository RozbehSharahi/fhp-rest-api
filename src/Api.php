<?php

namespace LazerRest;

use ICanBoogie\Inflector;
use Lazer\Classes\Database;
use Lazer\Classes\Helpers\Validate;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class LazerRest\Api
 *
 * Api class is a wrapper for slim. Instead of defining routes
 * you define models. Routes will be generated automatically.
 *
 * It also handles the creation of Lazer-Databases for your models.
 *
 * Feel free to extend from this class and change functionallities.
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
    public function createModel($modelName, $modelConfig, $controllerName = DefaultController::class)
    {
        $modelName = $this->inflector->hyphenate($modelName);
        return $this
            ->createOrReplaceTable($modelName, $modelConfig)
            ->createRoutes($modelName, $modelConfig, $controllerName);
    }

    /**
     * Create or replace a Lazer table
     *
     * @param string $modelName
     * @param array $modelConfig
     * @return $this
     */
    public function createOrReplaceTable($modelName, $modelConfig)
    {
        try {
            Validate::table($modelName)->exists();
        } catch (\Exception $e) {
            Database::create($modelName, $modelConfig);
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
     * @param array $modelConfig
     * @param string $controllerName
     * @return $this
     */
    public function createRoutes($modelName, $modelConfig, $controllerName = DefaultController::class)
    {
        return $this
            ->assignHeaders()
            ->assignErrorHandling()
            ->createIndexRoute($modelName, $modelConfig, $controllerName)
            ->createShowRoute($modelName, $modelConfig, $controllerName)
            ->createUpdateRoute($modelName, $modelConfig, $controllerName)
            ->createCreateRoute($modelName, $modelConfig, $controllerName)
            ->createDeleteRoute($modelName, $modelConfig, $controllerName);
    }

    /**
     * /modelName (GET)
     *
     * @param $modelName
     * @param $modelConfig
     * @param $controllerName
     * @return $this
     */
    public function createIndexRoute($modelName, $modelConfig, $controllerName)
    {
        $modelPath = '/' . $this->inflector->pluralize(lcfirst($this->inflector->camelize($modelName)));
        $this->app->get($modelPath, function () use ($modelName, $modelConfig, $controllerName) {

            /** @var Request $request */
            $request = func_get_arg(0);
            /** @var Response $response */
            $response = func_get_arg(1);

            /** @var DefaultController $controller at least instance of */
            $controller = new $controllerName([
                'modelName' => $modelName,
                'modelConfig' => $modelConfig,
                'request' => $request,
            ]);

            return $response
                ->withJson($controller->indexAction(), null, JSON_PRETTY_PRINT);
        });
        return $this;
    }

    /**
     * /modelName/id (GET)
     *
     * @param $modelName
     * @param $modelConfig
     * @param $controllerName
     * @return $this
     */
    public function createShowRoute($modelName, $modelConfig, $controllerName)
    {
        $modelPath = '/' . $this->inflector->pluralize(lcfirst($this->inflector->camelize($modelName)));
        $this->app->get($modelPath . '/{id}',
            function () use ($modelName, $modelConfig, $controllerName) {

                /** @var Request $request */
                $request = func_get_arg(0);
                /** @var Response $response */
                $response = func_get_arg(1);
                /** @var array $args */
                $args = func_get_arg(2);

                /** @var DefaultController $controller */
                $controller = new $controllerName([
                    'modelName' => $modelName,
                    'modelConfig' => $modelConfig,
                    'request' => $request,
                ]);

                return $response
                    ->withJson($controller->showAction($args['id']), null, JSON_PRETTY_PRINT);
            });
        return $this;
    }

    /**
     * /modelName/id (PUT)
     *
     * @param $modelName
     * @param $modelConfig
     * @param $controllerName
     * @return $this
     */
    public function createUpdateRoute($modelName, $modelConfig, $controllerName)
    {
        $modelPath = '/' . $this->inflector->pluralize(lcfirst($this->inflector->camelize($modelName)));
        $this->app->put($modelPath . '/{id}',
            function () use ($modelName, $modelConfig, $controllerName) {

                /** @var Request $request */
                $request = func_get_arg(0);
                /** @var Response $response */
                $response = func_get_arg(1);
                /** @var array $args */
                $args = func_get_arg(2);

                /** @var DefaultController $controller */
                $controller = new $controllerName([
                    'modelName' => $modelName,
                    'modelConfig' => $modelConfig,
                    'request' => $request,
                ]);

                return $response
                    ->withJson($controller->updateAction($args['id']), null, JSON_PRETTY_PRINT);
            });
        return $this;
    }

    /**
     * /modelName (POST)
     *
     * @param $modelName
     * @param $modelConfig
     * @param $controllerName
     * @return $this
     */
    public function createCreateRoute($modelName, $modelConfig, $controllerName)
    {
        $modelPath = '/' . $this->inflector->pluralize(lcfirst($this->inflector->camelize($modelName)));
        $this->app->post($modelPath,
            function () use ($modelName, $modelConfig, $controllerName) {

                /** @var Request $request */
                $request = func_get_arg(0);
                /** @var Response $response */
                $response = func_get_arg(1);

                /** @var DefaultController $controller */
                $controller = new $controllerName([
                    'modelName' => $modelName,
                    'modelConfig' => $modelConfig,
                    'request' => $request,
                ]);

                return $response
                    ->withJson($controller->createAction(), null, JSON_PRETTY_PRINT);
            });
        return $this;
    }

    /**
     * /modelName/id (DELETE)
     *
     * @param $modelName
     * @param $modelConfig
     * @param $controllerName
     * @return $this
     */
    public function createDeleteRoute($modelName, $modelConfig, $controllerName)
    {
        $modelPath = '/' . $this->inflector->pluralize(lcfirst($this->inflector->camelize($modelName)));
        $this->app->delete($modelPath . '/{id}',
            function () use ($modelName, $modelConfig, $controllerName) {

                /** @var Request $request */
                $request = func_get_arg(0);
                /** @var Response $response */
                $response = func_get_arg(1);
                /** @var array $args */
                $args = func_get_arg(2);

                /** @var DefaultController $controller */
                $controller = new $controllerName([
                    'modelName' => $modelName,
                    'modelConfig' => $modelConfig,
                    'request' => $request,
                ]);

                return $response
                    ->withJson($controller->deleteAction($args['id']), null, JSON_PRETTY_PRINT);
            });
        return $this;
    }

    /**
     * Forward process to app
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     */
    public function process(ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->app->process($request, $response);
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
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }
}