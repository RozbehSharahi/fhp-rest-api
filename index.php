<?php
use Fhp\Rest\Api;
use Slim\App;

require_once('vendor/autoload.php');
define('LAZER_DATA_PATH', __DIR__ . '/database/');

$api = new Api(new App);

$api
    ->createModel('note', [
        'title' => 'string',
        'content' => 'string',
        'top' => 'string',
        'left' => 'string',
        'width' => 'string',
        'height' => 'string',
        'edited' => 'integer',
        'deleted' => 'boolean'
    ])
    ->run();