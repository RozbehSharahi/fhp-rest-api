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

namespace Fhp\Rest\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Fhp\Rest\Encoder\JsonEncoder;
use Fhp\Rest\Normalizer\EntityNormalizer;
use Fhp\Rest\Utils\ArrayUtils;
use Fhp\Rest\Utils\FileUtils;
use ICanBoogie\Inflector;

/**
 * Class Fhp\Rest\Repository\JsonRepository
 *
 * No documentation was created yet
 *
 * @author Rozbeh Chiryai Sharahi <rozbeh.sharahi@primeit.eu>
 * @package Fhp\Rest\Repository
 */
class JsonRepository implements ObjectRepository
{

    /**
     * @var string
     */
    static protected $directory;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var string
     */
    protected $tableDirectoryPath;

    /**
     * @var string
     */
    protected $fileSuffix = '.data.json';

    /**
     * @var Inflector
     */
    protected $inflector;

    /**
     * @var JsonEncoder
     */
    protected $encoder;

    /**
     * @var EntityNormalizer
     */
    protected $normalizer;

    /**
     * @var FileUtils
     */
    protected $fileUtils;

    /**
     * @var ArrayUtils
     */
    protected $arrayUtils;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var array
     */
    protected $store = [];

    /**
     * @var string
     */
    protected $lastCreatedId;

    /**
     * JsonRepository constructor.
     *
     * @param string $className
     * @param string $tableDirectoryPath
     * @param Inflector $inflector
     * @param JsonEncoder $encoder
     * @param EntityNormalizer $normalizer
     * @param ArrayUtils $arrayUtils
     * @param FileUtils $fileUtils
     * @throws \Exception
     */
    public function __construct(
        $className,
        $tableDirectoryPath = null,
        $inflector = null,
        $encoder = null,
        $normalizer = null,
        $arrayUtils = null,
        $fileUtils = null
    ) {
        $this->className = $className;
        $this->tableDirectoryPath = self::$directory ?: $tableDirectoryPath;
        $this->inflector = $inflector ?: Inflector::get();
        $this->encoder = $encoder ?: new JsonEncoder();
        $this->normalizer = $normalizer ?: new EntityNormalizer();
        $this->arrayUtils = $arrayUtils ?: new ArrayUtils();
        $this->fileUtils = $fileUtils ?: new FileUtils();

        // Assert tableDirectoryPath missing
        $this->assertValidConfiguration();

        // Create main database directory in case it does not exist
        $this->fileUtils->createDirectory($this->tableDirectoryPath);
    }

    /**
     * Actually it is not short :)
     *
     * @return string
     */
    public function getShortName()
    {
        return str_replace('/', '-', $this->inflector->hyphenate($this->getClassName()));
    }

    /**
     * Get the data out of the json file
     *
     * @return array
     * @throws \Exception
     */
    protected function loadData()
    {
        return file_exists($this->getFileName()) ? $this->encoder->decodeFile($this->getFileName()) : [];
    }

    /**
     * @return array
     */
    protected function getData()
    {
        if (is_null($this->data)) {
            $this->data = $this->loadData();
        }
        return $this->data;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->tableDirectoryPath . $this->getShortName() . $this->fileSuffix;
    }

    /**
     * Finds an object by its primary key / identifier.
     *
     * @param mixed $id The identifier.
     * @return object The object.
     */
    public function find($id)
    {
        // Check if store contains id already
        if (is_null($this->findInStore($id))) {

            // Get the row
            $row = $this->arrayUtils->findBy('id', $id, $this->getData());

            // In case data exists
            if ($row) {
                $entity = $this->normalizer->denormalize($row, $this->getClassName());
                $this->pushToStore($id, $entity);
            }

        }
        return $this->findInStore($id);
    }

    /**
     * Checks if an object exists
     *
     * @param $id
     * @return boolean
     */
    public function has($id)
    {
        return $this->arrayUtils->findBy('id', $id, $this->getData()) ? true : false;
    }

    /**
     * Finds all objects in the repository.
     *
     * @return array The objects.
     */
    public function findAll()
    {
        $all = [];
        foreach ($this->getData() as $row) {
            $all[] = $this->find($row['id']);
        }
        return $all;
    }

    /**
     * Finds objects by a set of criteria.
     *
     * Optionally sorting and limiting details can be passed. An implementation may throw
     * an UnexpectedValueException if certain values of the sorting or limiting details are
     * not supported.
     *
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return array The objects.
     * @throws \UnexpectedValueException
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {

    }

    /**
     * Finds a single object by a set of criteria.
     *
     * @param array $criteria The criteria.
     * @return object The object.
     */
    public function findOneBy(array $criteria)
    {

    }

    /**
     * Deletes an entity
     *
     * @param object $entity
     * @return $this
     */
    public function delete($entity)
    {
        $this->assert('You must pass an object to ' . __METHOD__, is_object($entity));

        $entityData = $this->normalizer->normalize($entity);

        if (!empty($entityData['id'])) {
            $this->removeFromStore($entity);
            $this->data = $this->arrayUtils->removeBy('id', $entityData['id'], $this->data);
        }

        return $this;
    }

    /**
     * Returns the class name of the object managed by the repository.
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param object $entity
     * @return $this
     * @throws \Exception
     */
    public function save($entity)
    {
        $this->assert('You must pass an object to ' . __METHOD__, is_object($entity));
        $entityData = $this->normalizer->normalize($entity);

        // Create id on new entities
        if (empty($entityData['id'])) {
            $newId = $this->getNewId();
            $entity = $this->normalizer->denormalize(['id' => $newId], $entity);
            $entityData['id'] = $newId;
        }
        // Push entity to store
        $this->pushToStore($entityData['id'], $entity);

        // Remove existing object with same id
        $this->data = $this->arrayUtils->removeBy('id', $entityData['id'], $this->getData());

        // Serialize entity
        $this->data[] = $this->normalizer->normalize($entity);

        // Last created id
        $this->lastCreatedId = $entityData['id'];

        // Return repository again
        return $this;
    }

    /**
     * @return $this
     */
    public function persist()
    {
        $this->assertValidConfiguration();

        $this->encoder->encodeFile($this->getFileName(), array_values($this->getData()));
        return $this;
    }

    /**
     * @return string
     */
    protected function getNewId()
    {
        return md5(time() . microtime());
    }

    /**
     * @return string
     */
    public function getLastCreatedId()
    {
        return $this->lastCreatedId;
    }

    /**
     * Returns the deserialized entity
     *
     * @return object|null
     */
    protected function findInStore($id)
    {
        $this->assertValidConfiguration();

        return !empty($this->store[$id]) ? $this->store[$id] : null;
    }

    /**
     * Set the deserialized entity
     *
     * @return object|null
     */
    protected function pushToStore($id, $entity)
    {
        $this->assertValidConfiguration();

        $this->store[$id] = $entity;
    }

    /**
     * @param object $entity
     * @return $this
     */
    protected function removeFromStore($entity)
    {
        unset($this->store[$this->normalizer->normalize($entity)['id']]);
        return $this;
    }

    /**
     * @return EntityNormalizer
     */
    public function getNormalizer()
    {
        return $this->normalizer;
    }

    /**
     * @throws \Exception
     */
    protected function assertValidConfiguration()
    {
        $this->assert('You must set a valid $className and $tableDirectoryPath',
            !empty($this->className) && !empty($this->tableDirectoryPath));
    }

    /**
     * @param string $message
     * @param string $condition
     * @throws \Exception
     */
    protected function assert($message, $condition)
    {
        if (!$condition) {
            throw new \Exception($message);
        }
    }

    /**
     * @return string
     */
    public static function getDirectory()
    {
        return self::$directory;
    }

    /**
     * @param string $directory
     */
    public static function setDirectory($directory)
    {
        self::$directory = $directory;
    }
}