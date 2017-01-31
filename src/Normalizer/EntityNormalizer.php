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

use Fhp\Rest\Interpreter\EntityInterpreter;

/**
 * Class Fhp\Rest\Normalizer\EntityNormalizer
 *
 * No documentation was created yet
 *
 * @author Rozbeh Chiryai Sharahi <rozbeh.sharahi@primeit.eu>
 * @package Fhp\Rest\Normalizer
 */
class EntityNormalizer
{

    /**
     * @var EntityInterpreter
     */
    protected $entityInterpreter;

    /**
     * EntityNormalizer constructor.
     *
     * @param EntityInterpreter $entityInterpreter
     */
    public function __construct($entityInterpreter = null)
    {
        $this->entityInterpreter = $entityInterpreter ?: new EntityInterpreter();
    }

    /**
     * @param array $data
     * @param string|object $entityName
     * @return object
     */
    public function denormalize($data, $entityName)
    {
        // Create new instance
        $entity = is_object($entityName) ? $entityName : new $entityName;
        $entityName = is_object($entityName) ? get_class($entityName) : $entityName;

        // Get setters
        $propertySetters = $this->entityInterpreter
            ->setEntityName($entityName)
            ->getPropertySetters();

        // Set properties by setters
        foreach ($propertySetters as $propertyName => $propertySetter) {
            if (isset($data[$propertyName])) {
                $entity->{$propertySetter}($data[$propertyName]);
            }
        }

        return $entity;
    }

    /**
     * @param object $entity
     * @return array
     */
    public function normalize($entity)
    {
        // Normalized data
        $data = [];

        // Get getters
        $propertyGetters = $this->entityInterpreter
            ->setEntityName(get_class($entity))
            ->getPropertyGetters();

        // Get properties by getters
        foreach ($propertyGetters as $propertyName => $propertyGetter) {
            $data[$propertyName] = $entity->{$propertyGetter}();
        }

        return $data;
    }

}