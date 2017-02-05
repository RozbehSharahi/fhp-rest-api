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

namespace Fhp\Rest\Convention;

use Doctrine\Common\Persistence\ObjectRepository;
use Fhp\Rest\Repository\JsonRepository;
use Fhp\Rest\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Fhp\Rest\Convention\ControllerTrait
 *
 * No documentation was created yet
 *
 * @author Rozbeh Chiryai Sharahi <rozbeh.sharahi@primeit.eu>
 * @package Fhp\Rest\Convention
 */
trait ControllerTrait
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
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Response|\Slim\Http\Response
     */
    protected $response;

    /**
     * @var ObjectRepository|JsonRepository
     */
    protected $repository;

    /**
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * @param string $entityName
     * @return $this
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;
        return $this;
    }

    /**
     * @return string
     */
    public function getNodeName()
    {
        return $this->nodeName;
    }

    /**
     * @param string $nodeName
     * @return $this
     */
    public function setNodeName($nodeName)
    {
        $this->nodeName = $nodeName;
        return $this;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param RequestInterface $request
     * @return $this
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param ResponseInterface|Response|\Slim\Http\Response $response
     * @return $this
     */
    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return ObjectRepository|JsonRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param ObjectRepository|JsonRepository $repository
     * @return $this
     */
    public function setRepository($repository)
    {
        $this->repository = $repository;
        return $this;
    }

    /**
     * @param JsonRepository $jsonRepository
     * @return $this
     */
    public function setJsonRepository(JsonRepository $jsonRepository)
    {
        $this->repository = $jsonRepository;
    }

}