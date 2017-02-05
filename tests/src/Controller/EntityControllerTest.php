<?php


use Fhp\Rest\Controller\EntityController;
use Fhp\Rest\Normalizer\EntityNormalizer;
use Fhp\Rest\Response;
use Fhp\Rest\Test\TestModel;

class EntityControllerTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     */
    public function testControllerIndexAction()
    {
        $controller = EntityController::create(TestModel::class, 'testModel')
            ->setResponse(Response::create(new EntityNormalizer(), 'testModel'));

        $this->assertArrayHasKey('testModels', $controller->indexAction()->getEntities());
    }

    /**
     *
     */
    public function testAllActions()
    {
        $controller = EntityController::create(TestModel::class, 'testModel')
            ->setResponse(Response::create(new EntityNormalizer(), 'testModel'));

        $controller->setMockedPayload([
            'testModel' => [
                'title' => 'Welcome test',
                'content' => 'Hello world',
                'deleted' => 'null',
            ]
        ]);

        $response = $controller->createAction()->getEntity();

        $createdId = $response['testModel']['id'];

        $response = $controller->showAction(
            null,
            null,
            ['id' => $createdId]
        );

        $this->assertEquals($response->getEntity()['testModel']['id'], $createdId);

        $controller->setMockedPayload([
            "testModel" => [
                "title" => "My new title"
            ]
        ]);

        $controller->updateAction(
            null,
            null,
            ['id' => $createdId]
        );

        $response = $controller->showAction(
            null,
            null,
            ['id' => $createdId]
        );

        $this->assertEquals($response->getEntity()['testModel']['title'], "My new title");

        $response = json_decode($controller->deleteAction(
            null,
            null,
            ['id' => $createdId]
        )->getBody(), true);

        $this->assertContains('deleted', $response['message']);
        $this->assertContains($createdId, $response['message']);

        try {
            $controller->showAction(
                null,
                null,
                ['id' => $createdId]
            );
        } catch (Exception $e) {
            $exception = $e;
        }

        $this->assertTrue(!empty($exception));
    }

}