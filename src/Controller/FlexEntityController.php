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

use Fhp\Rest\Normalizer\FlexEntityNormalizer;
use Fhp\Rest\Repository\JsonRepository;
use Fhp\Rest\Response;

/**
 * Class Fhp\Rest\Controller\FlexEntityController
 *
 * The default controller to be called if no specific controller exists for the model
 *
 * @package Fhp\Rest\Controller
 */
class FlexEntityController extends EntityController
{

    /**
     * @param string $entityName
     * @param string $nodeName
     * @param null $repository
     * @param null $request
     * @param null $response
     * @return $this
     */
    static public function create($entityName, $nodeName, $repository = null, $request = null, $response = null)
    {
        $repository = new JsonRepository($nodeName, null, null, null, new FlexEntityNormalizer());
        return parent::create(
            $entityName,
            $nodeName,
            $repository,
            $request,
            $response
        );
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
            $this->response = Response::create(new FlexEntityNormalizer(), $this->nodeName);
        } else {
            $this->response = $response;
        }
        return $this;
    }

    /**
     * Set json repository
     *
     * setJsonRepository will automatically add \Fhp\Rest\FlexEntityNormalizer to the
     * given $jsonRepository.
     *
     * @param JsonRepository $jsonRepository
     * @return $this
     */
    public function setJsonRepository(JsonRepository $jsonRepository)
    {
        $jsonRepository->setNormalizer(new FlexEntityNormalizer());
        $this->repository = $jsonRepository;
        return $this;
    }


}