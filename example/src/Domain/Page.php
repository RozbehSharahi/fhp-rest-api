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

namespace Rs\Domain;

/**
 * Class Rs\Domain\Notes
 *
 * No documentation was created yet
 *
 * @package Rs\Domain
 * @Fhp\Entity
 */
class Page
{

    /**
     * @var string
     * @Fhp\Rest\PropertyType\StringType
     */
    protected $id;

    /**
     * @var string
     * @Fhp\Rest\PropertyType\StringType
     */
    protected $url;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

}