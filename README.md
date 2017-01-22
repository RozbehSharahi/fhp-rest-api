# Lazer REST API
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

The following index.php has PUT, GET, POST, OPTION, DELETE
Routes. It's simple as that.

```php
// index.php
require_once('vendor/autoload.php');

header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, POST, OPTIONS, DELETE');

define('LAZER_DATA_PATH', __DIR__ . '/database/');

$lazerRest = new LazerRest\Api(new Slim\App);

// Post model definition
$lazerRest->createModel('post', [ // <-- Please use singular here
    'title' => 'string',
    'content' => 'string',
    'edited' => 'integer',
]);

$lazerRest->run();

```

### Defining own controllers

You may also define your own controller for the model and pass
it's name as the third parameter to `$lazerRest->createModel`.

Please extend from `LazerRest\Controller\DefaultController` and
 then feel free to override the basic functions `indexAction`,
 `showAction`, `updateAction`, `deleteAction`, `createAction`.

```php
// index.php

...

$lazerRest->createModel('post', [ // <-- Please use singular here
    'title' => 'string',
    'content' => 'string',
    'edited' => 'integer',
], \My\Own\PostController::class);

...
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

## Todos

* Implement tests