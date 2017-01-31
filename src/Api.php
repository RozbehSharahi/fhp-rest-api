<?php
/**
 * FHP REST API is a package for fast creation of REST APIs based on
 * JSON files.
 *
 * ------------------------------------------------------------------------
 *
 *  Copyright (c) 2017 - Rozbeh Chiryai Sharahi <rozbeh.sharahi@primeit.eu>
 *
 * ------------------------------------------------------------------------
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fhp\Rest;

use Fhp\Rest\Controller\EntityController;
use Fhp\Rest\PropertyType\BooleanType;
use Fhp\Rest\PropertyType\StringType;
use ICanBoogie\Inflector;
use Slim\App;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Fhp\Rest\Api
 *
 * Api class is a wrapper for slim. Instead of defining routes
 * you activate entities. Routes will be generated automatically.
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
     * @var string
     */
    protected $controllerNamespace;

    /**
     * @var array
     */
    protected $typeMapping = [
        BooleanType::class => 'boolean',
        StringType::class => 'string',
    ];

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
     * Creates and api with default configuration
     * You may use constructor your self by passing your own instances.
     *
     * @return Api
     */
    static public function create()
    {
        $app = new App();
        $inflector = Inflector::get();

        return new self($app, $inflector);
    }

    /**
     * Api constructor.
     *
     * @param App $app
     * @param Inflector $inflector
     * @throws \Exception
     */
    public function __construct(
        App $app,
        Inflector $inflector
    ) {
        $this->app = $app;
        $this->inflector = $inflector;
    }

    /**
     * Create a route + lazer table ...
     *
     * @param string $entityName
     * @param string $controllerName
     * @param string $nodeName
     * @return $this
     */
    public function activateEntity($entityName, $controllerName = EntityController::class, $nodeName = null)
    {
        $nodeName = $nodeName ?: $this->getEntityNodeName($entityName);
        $controller = new $controllerName($entityName, $nodeName);

        return $this
            ->createRoutes($nodeName, $controller);
    }

    /**
     * Returns a logical nodeName by entityName
     *
     * @param $entityName
     * @return string
     */
    public function getEntityNodeName($entityName)
    {
        $parts = explode('\\', $entityName);
        $shortName = $parts[count($parts) - 1];
        return $this->inflector->camelize($shortName, true);
    }

    /**
     * Creates the routes of a node
     *
     * @param string $nodeName
     * @param object $controller
     * @return $this
     */
    public function createRoutes($nodeName, $controller)
    {
        return $this
            ->createIndexRoute($nodeName, $controller)
            ->createShowRoute($nodeName, $controller)
            ->createUpdateRoute($nodeName, $controller)
            ->createCreateRoute($nodeName, $controller)
            ->createDeleteRoute($nodeName, $controller);
    }

    /**
     * /modelName (GET)
     *
     * @param string $nodeName
     * @param object $controller
     * @return $this
     */
    public function createIndexRoute($nodeName, $controller)
    {
        $entityPath = '/' . $this->inflector->pluralize(lcfirst($this->inflector->camelize($nodeName)));
        $this->app->get($entityPath, [$controller, 'indexAction']);
        return $this;
    }

    /**
     * /modelName/id (GET)
     *
     * @param string $nodeName
     * @param object $controller
     * @return $this
     */
    public function createShowRoute($nodeName, $controller)
    {
        $entityPath = '/' . $this->inflector->pluralize(lcfirst($this->inflector->camelize($nodeName)));
        $this->app->get($entityPath . '/{id}', [$controller, 'showAction']);
        return $this;
    }

    /**
     * /modelName/id (PUT)
     *
     * @param string $nodeName
     * @param object $controller
     * @return $this
     */
    public function createUpdateRoute($nodeName, $controller)
    {
        $entityPath = '/' . $this->inflector->pluralize(lcfirst($this->inflector->camelize($nodeName)));
        $this->app->put($entityPath . '/{id}', [$controller, 'updateAction']);
        return $this;
    }

    /**
     * /modelName (POST)
     *
     * @param string $nodeName
     * @param object $controller
     * @return $this
     */
    public function createCreateRoute($nodeName, $controller)
    {
        $entityPath = '/' . $this->inflector->pluralize(lcfirst($this->inflector->camelize($nodeName)));
        $this->app->post($entityPath, [$controller, 'createAction']);
        return $this;
    }

    /**
     * /modelName/id (DELETE)
     *
     * @param string $nodeName
     * @param object $controller
     * @return $this
     */
    public function createDeleteRoute($nodeName, $controller)
    {
        $entityPath = '/' . $this->inflector->pluralize(lcfirst($this->inflector->camelize($nodeName)));
        $this->app->delete($entityPath . '/{id}', [$controller, 'deleteAction']);
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
                    ->withJson([
                        "type" => "error",
                        "message" => $exception->getMessage(),
                    ], null, JSON_PRETTY_PRINT);
            };
        };
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
        $this
            ->assignErrorHandling()
            ->assignHeaders();

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
        $this
            ->assignErrorHandling()
            ->assignHeaders();

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