<?php

namespace Fhp\Rest\Test;

use Fhp\Rest\Repository\JsonRepository;
use org\bovigo\vfs\vfsStream;

require_once(__DIR__ . '/../vendor/autoload.php');

class TestModel
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
     * @return TestModel
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
     * @return TestModel
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
     * @return TestModel
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param boolean $deleted
     * @return TestModel
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
        return $this;
    }
}

vfsStream::setup('test-database/');

JsonRepository::setDirectory(vfsStream::url('test-database/'));