# FHP REST API [![Build Status](https://travis-ci.org/RozbehSharahi/fhp-rest-api.svg?branch=v1.2.0)](https://travis-ci.org/RozbehSharahi/fhp-rest-api) (Master [![Build Status](https://travis-ci.org/RozbehSharahi/fhp-rest-api.svg?branch=master)](https://travis-ci.org/RozbehSharahi/fhp-rest-api))

## Inspiration

FHP REST API was inspired by the project `greg0/lazer-database` which
 is a PHP library that provides the use of JSON files
 as a database.

## What is does

With `rozbehsharahi/fhp-rest-api` you can setup a simple REST API
that saves everything in JSON files and has no need for databases.

## Installation

```shell
$ composer require rozbehsharahi/fhp-rest-api
```

## How To

The following __index.php__ is enough to provide PUT, GET, POST, OPTION, DELETE
routes for your application:

```php
use Fhp\Rest\Api;
use Slim\App;

require_once('vendor/autoload.php');
define('LAZER_DATA_PATH', __DIR__ . '/database/');

$api = new Api(new App);

// Create a model
$api->createModel('post', [ // <-- use singular here
    'title' => 'string',
    'content' => 'string',
]);

// Run
$api->run();
```

### Defining custom model-controllers

You may also define your own controllers to handle model-requests. Just pass
your controller's class name as the third parameter to `$api->createModel`.

Please extend `Fhp\Rest\Controller` and
 feel free to override the basic actions:

 * `public function indexAction() {}`
 * `public function showAction($id) {}`
 * `public function updateAction($id) {}`
 * `public function deleteAction($id) {}`
 * `public function createAction($id) {}`

by calling `$api->createModel` like following:

```php
$api->createModel('post', [
    'title' => 'string',
    'content' => 'string',
    'edited' => 'integer',
], \My\Own\PostController::class); <-- your class name here
```

## Super fast start without Apache

No apache, No MySQL, just make sure PHP is installed on your machine.

Since FHP REST API is super independent you may start your server
 on almost any machine that supports PHP7+ (in future also PHP5.6+). Not even Apache is
 needed to get it started. Just change your directory to the projects root and go on with:

```shell
$ php -S localhost:8000
```

## Start with Apache

In case you want to start FHP REST API on an apache server, please add
the following `.htaccess` file to your project's root where
`index.php` is located.

```htaccess
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [QSA,L]
```

## Security issues / CORS
FHP REST API has a set of default-headers that are defined in `Fhp\Rest\Api`.
 These headers also contain `'Access-Control-Allow-Origin' => '*'` which is
 a security issue depending on the project.

Please make sure to configure your own header-settings
when going to production. You may set those inside your constructor parameters:

```php
$api = new Api(new App, [
    'headers' => [
        'Access-Control-Allow-Origin' => 'http://my-specific-doain.com'
    ]
]);
```

You may also use the setter:

```php
$api->setHeaders([
    'Content-Type' => 'application/json',
    'Access-Control-Allow-Origin' => 'http://my-specific-doain.com'
]);
```

## Todos and issues

* Better types handling
* Refactoring
