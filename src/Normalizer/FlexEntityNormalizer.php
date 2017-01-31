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

namespace Fhp\Rest\Normalizer;

/**
 * Class Fhp\Rest\Normalizer\FlexEntityNormalizer
 *
 * No documentation was created yet
 *
 * @author Rozbeh Chiryai Sharahi <rozbeh.sharahi@primeit.eu>
 * @package Fhp\Rest\Normalizer
 */
class FlexEntityNormalizer
{

    /**
     * @param array $data
     * @param string|object $entityName
     * @return object
     */
    public function denormalize($data, $entityName = null)
    {
        // Create new instance
        $entity = is_object($entityName) ? $entityName : new \stdClass();

        // Set properties
        foreach ($data as $propertyName => $propertyValue) {
            $entity->{$propertyName} = $propertyValue;
        }

        return $entity;
    }

    /**
     * @param object $entity
     * @return array
     */
    public function normalize($entity)
    {
        return (array)$entity;
    }

}