<?php

namespace Fhp\Rest;

use Lazer\Classes\Helpers\Validate;

/**
 * Class Fhp\Rest\Database
 *
 * Builds a wrapper for Lazer Database  to have a little bit better handling
 *
 * @package Fhp\Rest
 */
class Database extends \Lazer\Classes\Database
{

    /**
     * Database constructor.
     *
     * @param string $name Table name
     */
    public function __construct($name = 'system')
    {
        // Use system table to avoid exception on new instance without table
        if ($name == 'system' && !$this->hasTable($name)) {
            $this->createTable('system', ['name' => 'string']);
        }

        Validate::table($name)->exists();
        $this->name = $name;
        $this->setFields();
        $this->setPending();
    }

    /**
     * Create a new query
     *
     * @param $name
     * @return Database
     */
    public function createQuery($name = null)
    {
        return new self($name ? $name : $this->name);
    }

    /**
     * Create a new model (in fact that's the same as createQuery
     *
     * @param $name
     * @return Database
     */
    public function createModel($name = null)
    {
        return $this->createQuery($name);
    }

    /**
     * @param string|null $id
     * @return Database
     */
    public function find($id = null)
    {
        return parent::find($id);
    }

    /**
     * Check if table exists
     *
     * @param string $name
     * @return bool
     */
    public function hasTable($name)
    {
        try {
            Validate::table($name)->exists();
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * Create a new table
     *
     * @param $name
     * @param $fields
     * @throws \Exception
     * @throws \Lazer\Classes\LazerException
     * @return $this
     */
    public function createTable($name, $fields)
    {
        if ($this->hasTable($name)) {
            throw new \Exception('You tried to create a table that already exists: ' . $name);
        }
        $this->create($name, $fields);
        return $this;
    }

    /**
     * Setter for one or many properties.
     *
     * Lazer Database is pretty fast in throwing exceptions therefore this was
     * created.
     *
     * @param array $field
     * @param array $value
     * @return $this
     */
    public function set($field, $value = null)
    {
        if (is_array($field)) {
            $fields = $field;
            foreach ($fields as $field => $value) {
                $this->set($field, $value);
            }
        } else {
            if (in_array($field, $this->fields())) {
                try {
                    $this->{$field} = !empty($value) ? $value : null;
                } catch (\Exception $e) {

                }
            }
        }
        return $this;
    }

    /**
     * Save method chaining
     *
     * @return $this
     */
    public function save()
    {
        parent::save();
        return $this;
    }


    /**
     * Converts model to an array. Is equivalent to asArray only for
     * single rows/models.
     *
     * @return array
     */
    public function toArray()
    {
        $array = [];
        foreach ($this->fields() as $field) {
            try {
                $array[$field] = $this->{$field};
            } catch (\Exception $e) {

            }
        }
        return $array;
    }

}