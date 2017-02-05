<?php
/**
 * FHP REST API
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

use Doctrine\Common\Persistence\ObjectRepository;
use Fhp\Rest\Convention\ControllerTrait;
use Fhp\Rest\Normalizer\EntityNormalizer;
use Fhp\Rest\Repository\JsonRepository;
use Fhp\Rest\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

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
     * Controller has request and response
     *
     * Due to this request and response object will be set to controller on creation
     * in Fhp\Rest\Api
     */
    use ControllerTrait;

    /**
     * @var array
     */
    protected $mockedPayload;

    /**
     * @param string $entityName
     * @param string $nodeName
     * @param ObjectRepository $repository
     * @param RequestInterface $request
     * @param ResponseInterface|Response $response
     * @return $this
     */
    static public function create($entityName, $nodeName, $repository = null, $request = null, $response = null)
    {
        $repository = $repository ?: new JsonRepository($entityName);
        return new self(
            $entityName,
            $nodeName,
            $repository,
            $request,
            $response
        );
    }

    /**
     * EntityController constructor.
     *
     * @param string $entityName
     * @param string $nodeName
     * @param object $repository
     * @param RequestInterface $request
     * @param ResponseInterface|Response $response
     */
    public function __construct(
        $entityName = null,
        $nodeName = null,
        $repository = null,
        $request = null,
        $response = null
    ) {
        $this->entityName = $entityName;
        $this->nodeName = $nodeName;
        $this->repository = $repository;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @return ResponseInterface|Response
     */
    public function indexAction()
    {
        return $this->response->withEntities($this->repository->findAll());
    }

    /**
     * @return ResponseInterface|Response
     * @throws \Exception
     */
    public function showAction()
    {
        $args = func_get_arg(2);

        $this->assert('Entity with id ' . $args['id'] . ' does not exist.', $this->repository->has($args['id']));

        return $this->response->withEntity($this->repository->find($args['id']));
    }

    /**
     * @return ResponseInterface|Response
     * @throws \Exception
     */
    public function createAction()
    {
        $payload = $this->getPayload();

        $this->assert('Payload must be valid and not empty for ' . __METHOD__, !empty($payload) && is_array($payload));

        // Create new entity and save it
        $entity = $this->repository->getNormalizer()->denormalize($payload, $this->entityName);
        $this->repository->save($entity);
        $this->repository->persist();

        return $this->response->withEntity($entity);
    }

    /**
     * @return ResponseInterface|Response
     * @throws \Exception
     */
    public function updateAction()
    {
        $args = func_get_arg(2);
        $payload = $this->getPayload();

        $this->assert('No id has been given to find ' . $this->entityName, !empty($args['id']));
        $this->assert('Payload must be valid and not empty for ' . __METHOD__, !empty($payload) && is_array($payload));
        $this->assert('Entity with id ' . $args['id'] . ' not found.', $this->repository->has($args['id']));

        // Fill entity and save it
        $entity = $this->repository->find($args['id']);
        $this->repository->getNormalizer()->denormalize($payload, $entity);
        $this->repository->save($entity);
        $this->repository->persist();

        return $this->response->withEntity($entity);
    }

    /**
     * @return ResponseInterface
     */
    public function deleteAction()
    {
        $args = func_get_arg(2);

        // Delete entity
        $entity = $this->repository->find($args['id']);
        $this->repository->delete($entity);
        $this->repository->persist();

        return $this->response->withJson(['message' => $this->entityName . ' with id=' . $args['id'] . ' has been deleted']);
    }

    /**
     * Deactivation of response setting
     *
     * Entity controller must not allow any other response types apart from
     * Fhp\Rest\Response. Therefore we throw away all responses apart from
     * this responses that are instances of Fhp\Rest\Response
     *
     * @param Response $response
     * @return $this
     */
    public function setResponse($response)
    {
        if (!$response instanceof Response) {
            $this->response = Response::create(new EntityNormalizer(), $this->nodeName);
        } else {
            $this->response = $response;
        }
        return $this;
    }

    /**
     * Get body content of the request as array
     *
     * In case a mockedPayload by unit tests is set, this will be
     * the result of this getter.
     *
     * @return array
     */
    public function getPayload()
    {
        return $this->mockedPayload[$this->nodeName]
            ?: json_decode($this->request->getBody()->__toString(), true)[$this->nodeName];
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
     * @param array $payload
     * @return $this
     */
    public function setMockedPayload($payload)
    {
        $this->mockedPayload = $payload;
        return $this;
    }

}
