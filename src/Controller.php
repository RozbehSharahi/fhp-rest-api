<?php

namespace LazerRest;

use ICanBoogie\Inflector;

/**
 * Class LazerRest\Controller
 *
 * The default controller to be called if no specific controller exists for the model
 *
 * @package LazerRest
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
     * @var Database
     */
    protected $database;

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
        $this->nodeName = !empty($config['nodeName']) ? $config['nodeName'] : lcfirst($this->inflector->camelize($this->modelName));
        $this->database = !empty($config['database']) ? $config['database'] : new Database($this->modelName);
        $this->payload = !empty($config['payload']) ? $config['payload'] : $this->getPayload();

        // Asserts
        $this->assert('ModelName must not be empty in ' . __METHOD__, !empty($this->modelName));
    }

    /**
     * Default index action
     *
     * @return array
     */
    public function indexAction()
    {
        $models = $this->database->createQuery()
            ->findAll()
            ->asArray();

        return [$this->inflector->pluralize($this->nodeName) => $models];
    }

    /**
     * @param string $id
     * @return mixed
     * @throws \Exception
     */
    public function showAction($id)
    {
        $model = $this->database->createQuery()
            ->find($id);

        return [$this->nodeName => $model->toArray()];
    }

    /**
     * @return mixed
     */
    public function createAction()
    {
        $this->assert('Payload must be valid and not empty for ' . __METHOD__, !empty($this->getPayload()[$this->nodeName]));

        $model = $this->database->createQuery()
            ->set($this->getPayload()[$this->nodeName])
            ->set('edited', time());

        $model->save();
        return [$this->nodeName => $model->toArray()];
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function updateAction($id)
    {
        $this->assert('Payload must be valid and not empty for ' . __METHOD__, !empty($this->getPayload()[$this->nodeName]));

        $model = $this->database->createQuery()
            ->find($id)
            ->set($this->getPayload()[$this->nodeName])
            ->set('edited', time());

        $model->save();
        return [$this->nodeName => $model->toArray()];
    }

    /**
     * @param string $id
     * @return array
     */
    public function deleteAction($id)
    {
        $this->database->createQuery()
            ->find($id)
            ->delete();

        return ['message' => $this->modelName . ' with id=' . $id . ' has been deleted'];
    }

    /**
     * Assert function
     *
     * @param string $message
     * @param bool $condition
     * @throws \Exception
     */
    protected function assert($message, $condition)
    {
        if (!$condition) {
            throw new \Exception($message);
        }
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        if (is_null($this->payload)) {
            try {
                $this->payload = json_decode(file_get_contents('php://input'), true);
                //@codeCoverageIgnoreStart
            } catch (\Exception $e) {
                $this->payload = [];
                //@codeCoverageIgnoreEnd
            }
        }
        return $this->payload;
    }

}