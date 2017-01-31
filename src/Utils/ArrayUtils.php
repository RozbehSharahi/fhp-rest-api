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

namespace Fhp\Rest\Utils;

/**
 * Class Fhp\Rest\Utils\ArrayUtils
 *
 * No documentation was created yet
 *
 * @author Rozbeh Chiryai Sharahi <rozbeh.sharahi@primeit.eu>
 * @package Fhp\Rest\Utils
 */
class ArrayUtils
{

    /**
     * Finds first element by $field and $value
     *
     * @param string $field
     * @param mixed $value
     * @param $array
     * @return mixed
     */
    public function findBy($field, $value, $array)
    {
        foreach ($array as $element) {
            if ($element[$field] == $value) {
                return $element;
            }
        }
        return null;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param array $array
     * @return array
     */
    public function removeBy($field, $value, $array)
    {
        foreach ($array as $index => $element) {
            if ($element[$field] == $value) {
                unset($array[$index]);
            }
        }
        return $array;
    }

}