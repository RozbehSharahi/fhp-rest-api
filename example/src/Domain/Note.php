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
class Note
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
    protected $title;

    /**
     * @var string
     * @Fhp\Rest\PropertyType\StringType
     */
    protected $content;

    /**
     * @var string
     * @Fhp\Rest\PropertyType\StringType
     */
    protected $top;

    /**
     * @var string
     * @Fhp\Rest\PropertyType\StringType
     */
    protected $left;

    /**
     * @var string
     * @Fhp\Rest\PropertyType\StringType
     */
    protected $width;

    /**
     * @var string
     * @Fhp\Rest\PropertyType\StringType
     */
    protected $height;

    /**
     * @var boolean
     * @Fhp\Rest\PropertyType\BooleanType
     */
    protected $deleted;

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
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return string
     */
    public function getTop()
    {
        return $this->top;
    }

    /**
     * @param string $top
     * @return $this
     */
    public function setTop($top)
    {
        $this->top = $top;
        return $this;
    }

    /**
     * @return string
     */
    public function getLeft()
    {
        return $this->left;
    }

    /**
     * @param string $left
     * @return $this
     */
    public function setLeft($left)
    {
        $this->left = $left;
        return $this;
    }

    /**
     * @return string
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param string $width
     * @return $this
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @return string
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param string $height
     * @return $this
     */
    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param boolean $deleted
     * @return $this
     */
    public function setDeleted($deleted)
    {
        $this->deleted = (boolean)$deleted;
        return $this;
    }

}