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

namespace Fhp\Rest\Interpreter;

use ICanBoogie\Inflector;

/**
 * Class Fhp\Rest\EntityInterpreter
 *
 * No documentation was created yet
 *
 * @package Fhp\Rest
 */
class EntityInterpreter
{

    /**
     * @var Inflector
     */
    protected $inflector;

    /**
     * @var string
     */
    protected $entityName;

    /**
     * @var array
     */
    protected $entityReflections = [];

    /**
     * EntityInterpreter constructor.
     *
     * @param string $entityName
     * @param Inflector $inflector
     * @param PropertyInterpreter $propertyInterpreter
     */
    public function __construct(
        $entityName = null,
        Inflector $inflector = null,
        PropertyInterpreter $propertyInterpreter = null
    ) {
        $this->entityName = $entityName;
        $this->inflector = $inflector ?: Inflector::get();
        $this->propertyInterpreter = $propertyInterpreter ?: new PropertyInterpreter();
    }

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
     * Get entity path
     *
     * ```/entityName```
     *
     * @return string
     */
    public function getPath()
    {
        return '/' . lcfirst($this->inflector->pluralize($this->getEntityReflection()->getShortName()));
    }

    /**
     * Returns an array similar to following:
     * ```php
     * [
     *      "title" => "Fhp\PropertyType\StingType"
     * ]
     * ```
     *
     * @param array $map
     * @return array
     */
    public function getProperties($map = [])
    {
        $properties = [];
        foreach ($this->getEntityReflection()->getProperties() as $propertyReflection) {
            $name = $propertyReflection->getName();
            $type = $this->propertyInterpreter
                ->setPropertyReflection($propertyReflection)
                ->getType();
            $properties[$name] = !empty($map[$type]) ? $map[$type] : $type;
        }
        return $properties;
    }

    /**
     * @return array
     */
    public function getPropertySetters()
    {
        $propertySetters = [];
        foreach ($this->getProperties() as $propertyName => $propertyType) {
            if (method_exists($this->entityName, 'set' . ucfirst($propertyName))) {
                $propertySetters[$propertyName] = 'set' . ucfirst($propertyName);
            }
        }
        return $propertySetters;
    }

    /**
     * @return array
     */
    public function getPropertyGetters()
    {
        $propertyGetters = [];
        foreach ($this->getProperties() as $propertyName => $propertyType) {
            if (method_exists($this->entityName, 'get' . ucfirst($propertyName))) {
                $propertyGetters[$propertyName] = 'get' . ucfirst($propertyName);
            }
        }
        return $propertyGetters;
    }

    /**
     * Get entity reflection for a class (cached)
     *
     * @return \ReflectionClass
     */
    public function getEntityReflection()
    {
        if (empty($this->entityReflections[$this->entityName])) {
            $this->entityReflections[$this->entityName] = new \ReflectionClass($this->entityName);
        }
        return $this->entityReflections[$this->entityName];
    }

    /**
     * @return PropertyInterpreter|null
     */
    public function getPropertyInterpreter()
    {
        return $this->propertyInterpreter;
    }

    /**
     * @param PropertyInterpreter|null $propertyInterpreter
     */
    public function setPropertyInterpreter($propertyInterpreter)
    {
        $this->propertyInterpreter = $propertyInterpreter;
    }

}