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

use Fhp\Rest\Normalizer\FlexEntityNormalizer;
use Fhp\Rest\Repository\JsonRepository;
use ICanBoogie\Inflector;

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
     * FlexEntityController constructor.
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
        $normalizer = new FlexEntityNormalizer();
        $repository = new JsonRepository($nodeName, null, null, null, $normalizer);
        parent::__construct($entityName, $nodeName, $repository, $inflector, $normalizer);
    }

}