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
    public function __construct($name)
    {
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