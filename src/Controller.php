<?php

namespace Fhp\Rest;

use ICanBoogie\Inflector;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Fhp\Rest\Controller
 *
 * The default controller to be called if no specific controller exists for the model
 *
 * @package Fhp\Rest
 */
class Controller
{

    /**
     * @var string
     */
    protected $modelName;

    /**
     * @var string
     */
    protected $nodeName;

    /**
     * @var Inflector
     */
    protected $inflector;

    /**
     * @var Database
     */
    protected $database;

    /**
     * This is for testing to mock a payload
     *
     * @var array
     */
    protected $mockedPayload;

    /**
     * Controller constructor.
     *
     * @param array $config
     * @throws \Exception
     */
    public function __construct($config = array())
    {
        $this->inflector = !empty($config['inflector']) ? $config['inflector'] : Inflector::get();
        $this->modelName = !empty($config['modelName']) ? $config['modelName'] : $this->modelName;
        $this->nodeName = !empty($config['nodeName']) ? $config['nodeName'] : lcfirst($this->inflector->camelize($this->modelName));
        $this->database = !empty($config['database']) ? $config['database'] : new Database($this->modelName);

        // Asserts
        $this->assert('ModelName must not be empty in ' . __METHOD__, !empty($this->modelName));
    }

    /**
     * Default index action
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function indexAction($request = null, $response = null, $args = [])
    {
        $models = $this->database->createQuery()
            ->findAll()
            ->asArray();

        return $this->response([$this->inflector->pluralize($this->nodeName) => $models]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function showAction($request = null, $response = null, $args = [])
    {
        $model = $this->database->createQuery()
            ->find($args['id']);

        return $this->response([$this->nodeName => $model->toArray()]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     * @throws \Exception
     */
    public function createAction($request = null, $response = null, $args = [])
    {
        $payload = $this->getPayload($request, $this->nodeName);
        $this->assert('Payload must be valid and not empty for ' . __METHOD__, !empty($payload) && is_array($payload));

        $model = $this->database->createQuery()
            ->set($payload)
            ->set('edited', time())
            ->save();

        return $this->response([$this->nodeName => $model->toArray()]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     * @throws \Exception
     */
    public function updateAction($request = null, $response = null, $args = [])
    {
        $payload = $this->getPayload($request, $this->nodeName);
        $this->assert('Payload must be valid and not empty for ' . __METHOD__, !empty($payload) && is_array($payload));

        $model = $this->database->createQuery()
            ->find($args['id'])
            ->set($payload)
            ->set('edited', time())
            ->save();

        return $this->response([$this->nodeName => $model->toArray()]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function deleteAction($request = null, $response = null, $args = [])
    {
        $this->database->createQuery()
            ->find($args['id'])
            ->delete();

        return $this->response(['message' => $this->modelName . ' with id=' . $args['id'] . ' has been deleted']);
    }

    /**
     * Assert function
     *
     * @param string $message
     * @param bool $condition
     * @throws \Exception
     */
    protected function assert($message, $condition)
    {
        if (!$condition) {
            throw new \Exception($message);
        }
    }

    /**
     * @param array $data
     * @param int $status
     * @param int $encodingOptions
     * @return Response
     */
    protected function response($data, $status = 200, $encodingOptions = JSON_PRETTY_PRINT)
    {
        $response = new Response();
        return $response->withJson($data, $status, $encodingOptions);
    }

    /**
     * @param Request $request
     * @param string $nodeName
     * @return array
     */
    public function getPayload($request = null, $nodeName = null)
    {
        if (!is_null($this->mockedPayload)) {
            return $nodeName ? $this->mockedPayload[$nodeName] : $this->mockedPayload;
        }

        $payload = json_decode($request->getBody()->__toString(), true);
        return $nodeName ? $payload[$nodeName] : $payload;
    }

    /**
     * @param array $payload
     * @return $this
     */
    public function setMockedPayload($payload)
    {
        $this->mockedPayload = $payload;
        return $this;
    }

}