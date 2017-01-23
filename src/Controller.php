<?php

namespace LazerRest;

use ICanBoogie\Inflector;
use Lazer\Classes\Core_Database;
use Lazer\Classes\Database;
use Slim\Http\Request;

/**
 * Class LazerRest\Controller
 *
 * The default controller to be called if no specific controller exists for the model
 *
 * @package LazerRest\Controller
 */
class Controller
{

    /**
     * @var string
     */
    protected $modelName;

    /**
     * @var string
     */
    protected $nodeName;

    /**
     * @var Inflector
     */
    protected $inflector;

    /**
     * @var array
     */
    protected $payload;

    /**
     * @var Request
     */
    protected $request;

    /**
     * Controller constructor.
     *
     * @param array $config
     * @throws \Exception
     */
    public function __construct($config = array())
    {
        $this->inflector = !empty($config['inflector']) ? $config['inflector'] : Inflector::get();
        $this->modelName = !empty($config['modelName']) ? $config['modelName'] : $this->modelName;
        $this->payload = !empty($config['payload']) ? $config['payload'] : $this->getPayload();
        $this->nodeName = !empty($config['nodeName']) ? $config['nodeName'] : lcfirst($this->inflector->camelize($this->modelName));
        $this->request = !empty($config['request']) ? $config['request'] : null;

        // Asserts
        if (empty($this->modelName)) {
            throw new \Exception('ModelName must not be null for ' . __METHOD__);
        }
    }

    /**
     * Default index action
     *
     * @return array
     */
    public function indexAction()
    {
        $models = Database::table($this->inflector->hyphenate($this->modelName))->findAll()->asArray();
        return [$this->inflector->pluralize($this->nodeName) => $models];
    }

    /**
     * @param string $id
     * @return mixed
     * @throws \Exception
     */
    public function showAction($id)
    {
        $model = Database::table($this->inflector->hyphenate($this->modelName))->find($id);
        return [$this->nodeName => $this->modelToArray($model)];
    }

    /**
     * @return mixed
     */
    public function createAction()
    {
        // Prepare
        $model = Database::table($this->inflector->hyphenate($this->modelName));

        // Fill up
        foreach ($this->getPayload()[$this->nodeName] as $propertyName => $propertyValue) {
            if (in_array($propertyName, $model->fields())) {
                try {
                    $model->{$propertyName} = !empty($propertyValue) ? $propertyValue : '';
                } catch (\Exception $e) {

                }
            }
        }

        // Edited field
        if (in_array('edited', $model->fields())) {
            $model->edited = time();
        }

        // Save the model
        $model->save();

        // Return the model
        return [$this->nodeName => $this->modelToArray($model)];
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function updateAction($id)
    {
        // Prepare
        $model = Database::table($this->inflector->hyphenate($this->modelName))->find($id);

        // Fill up
        foreach ($this->getPayload()[$this->nodeName] as $propertyName => $propertyValue) {
            if (in_array($propertyName, $model->fields())) {
                try {
                    $model->{$propertyName} = !empty($propertyValue) ? $propertyValue : '';
                } catch (\Exception $e) {

                }
            }
        }

        // Edited field
        if (in_array('edited', $model->fields())) {
            $model->edited = time();
        }

        // Save the model
        $model->save();

        // Return the model
        return [$this->nodeName => $this->modelToArray($model)];
    }

    /**
     * @param string $id
     * @return array
     */
    public function deleteAction($id)
    {
        Database::table($this->inflector->hyphenate($this->modelName))->find($id)->delete();
        return ['message' => $this->modelName . ' with id=' . $id . ' has been deleted'];
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        if (is_null($this->payload)) {
            try {
                $this->payload = json_decode(file_get_contents('php://input'), true);
            } catch (\Exception $e) {
                $this->payload = [];
            }
        }
        return $this->payload;
    }

    /**
     * @param Core_Database $row
     * @return array
     */
    public function modelToArray($row)
    {
        $rowArray = [];
        foreach ($row->fields() as $field) {
            try {
                $rowArray[$field] = $row->{$field};
            } catch (\Exception $e) {

            }
        }
        return $rowArray;
    }

}