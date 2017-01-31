<?php
use Fhp\Rest\Api;
use Fhp\Rest\Controller\FlexEntityController;
use Fhp\Rest\Repository\JsonRepository;
use Rs\Domain\Page;

require_once('vendor/autoload.php');

JsonRepository::setDirectory(__DIR__ . '/database/');

Api::create()
    ->activateEntity('note', FlexEntityController::class)
    ->activateEntity(Page::class)
    ->run();