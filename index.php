<?php

require_once('vendor/autoload.php');

define('LAZER_DATA_PATH', __DIR__ . '/database/');

$api = new LazerRest\Api(new Slim\App);

// Create a model
$api->createModel('note', [
    'title' => 'string',
    'content' => 'string',
    'top' => 'string',
    'left' => 'string',
    'width' => 'string',
    'height' => 'string',
    'edited' => 'integer',
    'deleted' => 'boolean'
]);

// Run
$api->run();