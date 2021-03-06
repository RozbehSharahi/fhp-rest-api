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

namespace Fhp\Rest\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Fhp\Rest\Encoder\JsonEncoder;
use Fhp\Rest\Normalizer\EntityNormalizer;
use Fhp\Rest\Normalizer\FlexEntityNormalizer;
use Fhp\Rest\Utils\ArrayUtils;
use Fhp\Rest\Utils\FileUtils;
use ICanBoogie\Inflector;

/**
 * Class Fhp\Rest\Repository\JsonRepository
 *
 * I will add a description to this. Maybe i will also move this class to an
 * own git repository, since it is quite cool and can be used for many projects.
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
    protected $entityName;

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
     * Create new json repository
     *
     * You may use this static method for easier creation of a new instance.
     * The created repository will get default configurations.
     *
     * @param $entityName
     * @param EntityNormalizer|FlexEntityNormalizer|object $normalizer
     * @param JsonEncoder|object $encoder
     * @return JsonRepository
     */
    static public function create($entityName, $normalizer = null, $encoder = null)
    {
        $directory = self::$directory;
        $normalizer = $normalizer ?: new EntityNormalizer();
        $encoder = $encoder ?: new JsonEncoder();

        // Instance with default configuration
        return new self(
            $entityName,
            $directory,
            $normalizer,
            $encoder,
            Inflector::get(),
            new ArrayUtils(),
            new FileUtils()
        );
    }

    /**
     * JsonRepository constructor.
     *
     * @param string $entityName
     * @param string $tableDirectoryPath
     * @param EntityNormalizer $normalizer
     * @param JsonEncoder $encoder
     * @param Inflector $inflector
     * @param ArrayUtils $arrayUtils
     * @param FileUtils $fileUtils
     * @throws \Exception
     */
    public function __construct(
        $entityName,
        $tableDirectoryPath = null,
        $normalizer = null,
        $encoder = null,
        $inflector = null,
        $arrayUtils = null,
        $fileUtils = null
    ) {
        $this->entityName = $entityName;
        $this->tableDirectoryPath = self::$directory ?: $tableDirectoryPath;
        $this->normalizer = $normalizer;
        $this->encoder = $encoder;
        $this->inflector = $inflector;
        $this->arrayUtils = $arrayUtils;
        $this->fileUtils = $fileUtils;

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
        return str_replace('/', '-', $this->inflector->hyphenate($this->getEntityName()));
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
                $entity = $this->normalizer->denormalize($row, $this->getEntityName());
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
     * Get entity name
     *
     * Returns the entity name of the object managed by the repository.
     *
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * Alias for getEntityName
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->getEntityName();
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
     * @return EntityNormalizer|FlexEntityNormalizer|object
     */
    public function getNormalizer()
    {
        return $this->normalizer;
    }

    /**
     * @param EntityNormalizer|FlexEntityNormalizer|object $normalizer
     * @return $this
     */
    public function setNormalizer($normalizer)
    {
        $this->normalizer = $normalizer;
        return $this;
    }

    /**
     * @throws \Exception
     */
    protected function assertValidConfiguration()
    {
        $this->assert('You must set a valid $entityName and $tableDirectoryPath',
            !empty($this->entityName) && !empty($this->tableDirectoryPath));
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