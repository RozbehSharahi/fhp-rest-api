# Lazer-REST-API [![Build Status](https://travis-ci.org/RozbehSharahi/lazer-rest-api.svg?branch=v1.1.0)](https://travis-ci.org/RozbehSharahi/lazer-rest-api) (Master [![Build Status](https://travis-ci.org/RozbehSharahi/lazer-rest-api.svg?branch=master)](https://travis-ci.org/RozbehSharahi/lazer-rest-api))

## Inspiration

Lazer-REST-API was inspired by the project `greg0/lazer-database`, which
 is PHP library that provides using JSON files
 as a database.

## What is does

With `rozbehsharahi/lazer-rest-api` you can setup a super easy REST-API
that saves all data in JSON files and does not need any set up of
any databases.

## Installation

```shell
$ composer require rozbehsharahi/lazer-rest-api
```

## How To

The following __index.php__ is enough to provide PUT, GET, POST, OPTION, DELETE
routes for your application:

```php
use LazerRest\Api;
use Slim\App;
require_once('vendor/autoload.php');
define('LAZER_DATA_PATH', __DIR__ . '/database/');

$api = new Api(new App);

// Post model definition
$api->createModel('post', [ // <-- Please use singular here
    'title' => 'string',
    'content' => 'string',
    'edited' => 'integer',
]);

$api->run();

```

### Defining custom model-controllers

You may also define your own controller for the models. Just pass
the class name as third parameter to `$api->createModel`.

Please extend from `LazerRest\Controller\DefaultController` and
 then feel free to override the basic functions:

 * `public function indexAction() {}`
 * `public function showAction($id) {}`
 * `public function updateAction($id) {}`
 * `public function deleteAction($id) {}`
 * `public function createAction($id) {}`

by calling `$api->createModel` like following:

```php
$api->createModel('post', [ // <-- Please use singular here
    'title' => 'string',
    'content' => 'string',
    'edited' => 'integer',
], \My\Own\PostController::class);
```

## Super fast start without Apache

No apache, No MySQL, just make sure PHP is installed on your mashine.

Since Lazer REST API is super independent you may start a server
 on almost any mashine that support PHP7. Not even Apache is
 needed to get it started. Just change your directory to the projects root and go on with:

```shell
php -S localhost:8000
```

## Start with Apache

In case you want to start the REST API with Apache you will
need to add an `.htaccess` file to the projects root where your
`index.php` is defined.

```htaccess
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [QSA,L]
```

## Security issues / CORS
`lazer-rest-api` has a set of default headers defined in `LazerRest\Api`
that also contain `'Access-Control-Allow-Origin' => '*'`. This is
a security issue. Please make sure to configure your own header settings
when going to production.

You may set your headers inside the constructor parameters:

```php
$api = new Api(new App, [
    'Access-Control-Allow-Origin' => 'http://my-specific-doain.com'
]);
```

You may also set your headers using the setter:

```php
$api = new Api(new App);
$api->setHeaders([
    'Content-Type' => 'application/json',
    'Access-Control-Allow-Origin' => 'http://my-specific-doain.com'
]);

```

## Todos and Issues

* PHP5.6 fails at the moment. In PHP5.6 request content is empty after being read once. Due to
 bad architecture of Lazer-REST-API payload is empty at the moment the Controllers are called.
 The next release will integrate `\Slim\Http\Request` to have the payload available.
