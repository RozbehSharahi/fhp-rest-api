# Lazer REST API [![Build Status](https://travis-ci.org/RozbehSharahi/lazer-rest-api.svg?branch=v1.0.0)](https://travis-ci.org/RozbehSharahi/lazer-rest-api) (Master [![Build Status](https://travis-ci.org/RozbehSharahi/lazer-rest-api.svg?branch=master)](https://travis-ci.org/RozbehSharahi/lazer-rest-api))

## Inspiration

Lazer REST API was inspired by the project `greg0/lazer-database`.
Lazer Database is PHP Library that provides using JSON files
like a database. Therefore it is completely independent from
databases and apache ...

__Caution!: The project is still in progress and very new.__

## What is does

With `lazer-rest-api` you can setup a super easy REST API
that saves all data in JSON-Files. No MySQL Configuration and Setup
is needed at all.

## Installation

```php
// This is for the future

composer require rozbehsharahi/lazer-rest-api
```

## How To

The following __index.php__ has PUT, GET, POST, OPTION, DELETE
Routes. It's simple as that.

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

### Defining own controllers

You may also define your own controller for the model and pass
it's name as the third parameter to `$api->createModel`.

Please extend from `LazerRest\Controller\DefaultController` and
 then feel free to override the basic functions `indexAction`,
 `showAction`, `updateAction`, `deleteAction`, `createAction`.

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

* Slim causes on __PHP5.6__ that payload cannot be read anymore. Therefore
POST and PUT Requests do not work on PHP5.6. This is a big issue
and will be fixed with the next release and tests will be written for this
too.
