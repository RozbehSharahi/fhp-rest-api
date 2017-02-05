<?php


use Fhp\Rest\Controller\FlexEntityController;
use Fhp\Rest\Normalizer\FlexEntityNormalizer;
use Fhp\Rest\Response;

class FlexEntityControllerTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     */
    public function testControllerIndexAction()
    {
        $controller = FlexEntityController::create('testModel', 'testModel')
            ->setResponse(Response::create(new FlexEntityNormalizer(), 'testModel'));

        $this->assertArrayHasKey('testModels', $controller->indexAction()->getEntities());
    }

    /**
     *
     */
    public function testAllActions()
    {
        $controller = FlexEntityController::create('testModel', 'testModel')
            ->setResponse(Response::create(new FlexEntityNormalizer(), 'testModel'));

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