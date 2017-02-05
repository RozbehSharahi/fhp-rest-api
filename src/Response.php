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

namespace Fhp\Rest;

use Fhp\Rest\Normalizer\EntityNormalizer;
use Fhp\Rest\Normalizer\FlexEntityNormalizer;
use ICanBoogie\Inflector;
use Psr\Http\Message\StreamInterface;
use Slim\Http\Response as SlimResponse;
use Slim\Interfaces\Http\HeadersInterface;

/**
 * Class Fhp\Rest\Response
 *
 * This is a response object specially made for Fhp\Rest\Entities. For better testing
 * and handling you will be able to use methods:
 *
 * * ->withEntity(...)
 * * ->withEntities(...)
 *
 * This will make the code more readable and you will can use method
 *
 * * ->getEntity()
 * * ->getEntities()
 *
 * for easier testing.
 *
 * @author Rozbeh Chiryai Sharahi <rozbeh.sharahi@primeit.eu>
 * @package Fhp\Rest
 */
class Response extends SlimResponse
{
    /**
     * @var EntityNormalizer
     */
    protected $normalizer;

    /**
     * @var string
     */
    protected $nodeName;

    /**
     * @var Inflector
     */
    protected $inflector;

    /**
     * @var object
     */
    protected $entity;

    /**
     * @var array
     */
    protected $entities;

    /**
     * Create a new response object
     *
     * @param EntityNormalizer|FlexEntityNormalizer|object $normalizer
     * @param string $nodeName
     * @return $this
     */
    static public function create($normalizer, $nodeName)
    {
        return new self(200, null, null, $normalizer, $nodeName, Inflector::get());
    }

    /**
     * Create new Response object for Fhp\Rest Entities.
     *
     * For creating a new instance of this class you will need to define a normalizer
     * and node name. By this configuration \Fhp\Rest\Response is able to create
     * json responses for your entities.
     *
     * @param int $status
     * @param null|\Slim\Interfaces\Http\HeadersInterface $headers
     * @param null|\Psr\Http\Message\StreamInterface $body
     * @param EntityNormalizer $normalizer
     * @param string $nodeName
     * @param Inflector $inflector
     */
    public function __construct(
        $status = 200,
        HeadersInterface $headers = null,
        StreamInterface $body = null,
        $normalizer,
        $nodeName,
        $inflector
    ) {
        $this->normalizer = $normalizer;
        $this->nodeName = $nodeName;
        $this->inflector = $inflector;
        parent::__construct($status, $headers, $body);
    }

    /**
     * Will turn the given entity to json using the configured normalizer and
     * return a new Response instance.
     *
     * @param object $entity
     * @param integer $status
     * @param int $encodingOptions
     * @return $this
     */
    public function withEntity($entity, $status = 200, $encodingOptions = JSON_PRETTY_PRINT)
    {
        $normalizedEntity = $this->normalizer->normalize($entity);
        $data = [$this->nodeName => $normalizedEntity];
        $this->entity = $data;

        return parent::withJson($data, $status, $encodingOptions);
    }

    /**
     * Will turn the given entities to json using the configured normalizer and
     * return a new Response instance.
     *
     * @param array $entities
     * @param integer $status
     * @param int $encodingOptions
     * @return $this
     */
    public function withEntities($entities, $status = 200, $encodingOptions = JSON_PRETTY_PRINT)
    {
        $normalizedEntities = [];
        foreach ($entities as $entity) {
            $normalizedEntities[] = $this->normalizer->normalize($entity);
        }
        $data = [$this->inflector->pluralize($this->nodeName) => $normalizedEntities];
        $this->entities = $data;

        return parent::withJson($data, $status, $encodingOptions);
    }

    /**
     * Get the last set entity
     *
     * @return object
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Get the last set entities
     *
     * @return array
     */
    public function getEntities()
    {
        return $this->entities;
    }
}