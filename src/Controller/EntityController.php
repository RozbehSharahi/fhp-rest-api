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

namespace Fhp\Rest\Controller;

use Fhp\Rest\Normalizer\EntityNormalizer;
use Fhp\Rest\Repository\JsonRepository;
use ICanBoogie\Inflector;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Fhp\Rest\Controller\EntityController
 *
 * The default controller to be called if no specific controller exists for the model
 *
 * @package Fhp\Rest\Controller
 */
class EntityController
{

    /**
     * @var string
     */
    protected $entityName;

    /**
     * @var string
     */
    protected $nodeName;

    /**
     * @var Inflector
     */
    protected $inflector;

    /**
     * @var JsonRepository
     */
    protected $repository;

    /**
     * @var EntityNormalizer
     */
    protected $normalizer;

    /**
     * This is for testing to mock a payload
     *
     * @var array
     */
    protected $mockedPayload;

    /**
     * EntityController constructor.
     *
     * @param string $entityName
     * @param string $nodeName
     * @param JsonRepository $repository
     * @param Inflector $inflector
     * @param null $normalizer
     * @throws \Exception
     */
    public function __construct(
        $entityName,
        $nodeName = null,
        $repository = null,
        $inflector = null,
        $normalizer = null
    ) {
        $this->entityName = $entityName ?: $this->entityName;
        $this->nodeName = $nodeName ?: $this->nodeName;
        $this->repository = $repository ?: $this->repository ?: new JsonRepository($this->entityName);
        $this->inflector = $inflector ?: $this->inflector ?: Inflector::get();
        $this->normalizer = $normalizer ?: $this->normalizer ?: new EntityNormalizer();

        // Asserts
        $this->assert('ModelName must not be empty in ' . __METHOD__, !empty($this->entityName));
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
        return $this->responseEntities($this->repository->findAll());
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function showAction($request = null, $response = null, $args = [])
    {
        $this->assert('Entity with id ' . $args['id'] . ' does not exist.', $this->repository->has($args['id']));
        return $this->responseEntity($this->repository->find($args['id']));
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

        // Create new entity
        $entity = $this->normalizer->denormalize($payload, $this->entityName);

        // Save entity
        $this->repository->save($entity);
        $this->repository->persist();

        return $this->responseEntity($entity);
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
        $entity = $this->repository->find($args['id']);
        $payload = $this->getPayload($request, $this->nodeName);

        $this->assert('Payload must be valid and not empty for ' . __METHOD__, !empty($payload) && is_array($payload));
        $this->assert('Entity with id ' . $args['id'] . ' not found.', !empty($entity));

        // Fill entity
        $this->normalizer->denormalize($payload, $entity);

        // Save entity
        $this->repository->save($entity);
        $this->repository->persist();

        return $this->responseEntity($entity);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function deleteAction($request = null, $response = null, $args = [])
    {
        $entity = $this->repository->find($args['id']);
        $this->repository->delete($entity);
        $this->repository->persist();
        return $this->responseJson(['message' => $this->entityName . ' with id=' . $args['id'] . ' has been deleted']);
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
     * @param object $entity
     * @param integer $status
     * @param integer $encodingOptions
     * @return Response
     */
    protected function responseEntity($entity, $status = 200, $encodingOptions = JSON_PRETTY_PRINT)
    {
        $response = new Response();
        $normalizedEntity = $this->normalizer->normalize($entity);
        return $response->withJson([$this->nodeName => $normalizedEntity], $status, $encodingOptions);
    }

    /**
     * @param array $entities
     * @param integer $status
     * @param integer $encodingOptions
     * @return Response
     */
    protected function responseEntities($entities, $status = 200, $encodingOptions = JSON_PRETTY_PRINT)
    {
        $response = new Response();
        $nodeName = $this->inflector->pluralize($this->nodeName);
        $normalizedEntities = [];

        // Normalize entities
        foreach ($entities as $entity) {
            $normalizedEntities[] = $this->normalizer->normalize($entity);
        }

        return $response->withJson([$nodeName => $normalizedEntities], $status, $encodingOptions);
    }

    /**
     * @param array $data
     * @param int $status
     * @param int $encodingOptions
     * @return Response
     */
    protected function responseJson($data, $status = 200, $encodingOptions = JSON_PRETTY_PRINT)
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