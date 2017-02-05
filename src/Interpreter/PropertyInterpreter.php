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

use Fhp\Rest\PropertyType\BooleanType;
use Fhp\Rest\PropertyType\StringType;
use ICanBoogie\Inflector;

/**
 * Class Fhp\Rest\Interpreter\PropertyInterpreter
 *
 * No documentation was created yet
 *
 * @package Fhp\Rest\Interpreter
 */
class PropertyInterpreter
{

    /**
     * @var Inflector
     */
    protected $inflector;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var string
     */
    protected $propertyName;

    /**
     * @var array
     */
    protected $typeMapping = [
        StringType::class => StringType::class,
        BooleanType::class => BooleanType::class,
    ];

    /**
     * @var array
     */
    protected $propertyReflections = [];

    /**
     * PropertyInterpreter constructor.
     *
     * @param string $className
     * @param string $propertyName
     * @param Inflector $inflector
     */
    public function __construct($className = null, $propertyName = null, Inflector $inflector = null)
    {
        $this->className = $className;
        $this->propertyName = $propertyName;
        $this->inflector = $inflector;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param string $className
     * @return $this
     */
    public function setClassName($className)
    {
        $this->className = $className;
        return $this;
    }

    /**
     * @return string
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * @param string $propertyName
     * @return $this
     */
    public function setPropertyName($propertyName)
    {
        $this->propertyName = $propertyName;
        return $this;
    }

    /**
     * This one searches for type annotations and returns them
     *
     * @return string
     */
    public function getType()
    {
        foreach ($this->typeMapping as $annotation => $type) {
            if (strpos($this->getPropertyReflection()->getDocComment(), $annotation) !== false) {
                return $type;
            }
        }
        return null;
    }

    /**
     * @return \ReflectionProperty
     */
    public function getPropertyReflection()
    {
        if (empty($this->propertyReflections[$this->propertyName])) {
            $this->propertyReflections[$this->getReflectionCacheKey()]
                = new \ReflectionProperty($this->getClassName(), $this->getPropertyName());
        }
        return $this->propertyReflections[$this->getReflectionCacheKey()];
    }

    /**
     * @param \ReflectionProperty $propertyReflection
     * @return $this
     */
    public function setPropertyReflection(\ReflectionProperty $propertyReflection)
    {
        $this->className = $propertyReflection->class;
        $this->propertyName = $propertyReflection->name;
        $this->propertyReflections[$this->getReflectionCacheKey()] = $propertyReflection;
        return $this;
    }

    /**
     * @return Inflector
     */
    public function getInflector()
    {
        return $this->inflector;
    }

    /**
     * @param Inflector $inflector
     */
    public function setInflector($inflector)
    {
        $this->inflector = $inflector;
    }

    /**
     * @return string
     */
    public function getReflectionCacheKey()
    {
        return $this->className . '::' . $this->propertyName;
    }

}