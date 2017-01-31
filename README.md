# FHP REST API [![Build Status](https://travis-ci.org/RozbehSharahi/fhp-rest-api.svg?branch=v2.0.0)](https://travis-ci.org/RozbehSharahi/fhp-rest-api) (Master [![Build Status](https://travis-ci.org/RozbehSharahi/fhp-rest-api.svg?branch=master)](https://travis-ci.org/RozbehSharahi/fhp-rest-api))

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

## How to

The following __index.php__ is enough to provide PUT, GET, POST, OPTION, DELETE
routes for your application on the `/posts` route:

```php
<?php
use Fhp\Rest\Api;
use Fhp\Rest\Repository\JsonRepository;
use Fhp\Rest\Controller\FlexEntityController;

require_once('vendor/autoload.php');

// Set json database folder
JsonRepository::setDirectory(__DIR__ . '/database/');

// Api call here
Api::create()
    ->activateEntity('post', FlexEntityController::class) // <-- use singular entity name
    ->run();
```

That's it!

### How to use entity classes

(Still experimental)

Using this way of configuring the FHP REST API is still very experimental. For
 instance you have to define fhp property type annotations, though they
 don't have any impact on the way a property is saved yet.

 ```php
 <?php
 use Fhp\Rest\Api;
 use Fhp\Rest\Repository\JsonRepository;

 require_once('vendor/autoload.php');

 // Set json database folder
 JsonRepository::setDirectory(__DIR__ . '/database/');

 // Api call here
 Api::create()
     ->activateEntity(My\Example\Page::class)
     ->run();
 ```

Your entity class should look like this:

```php
<?php
namespace My\Example;

/**
 * My Entity class
 *
 * @class Page
 */
class Page
{

    /**
     * @Fhp\Rest\PropertyType\StringType
     */
    protected $id;

    /**
     * @Fhp\Rest\PropertyType\StringType
     */
    protected $url;

    public function getId() { return $this->id; }

    public function setId($id) { $this->id = $id; return $this; }

    public function getUrl() { return $this->url; }

    public function setUrl($url) { $this->url = $url; return $this; }
}
```

At the moment it does not make any difference which property type you will use. But
 it is a good preparation for next releases. You will still have to annotate with
 one of the following annotations:

 * `@Fhp\Rest\PropertyType\StringType`
 * `@Fhp\Rest\PropertyType\BooleanType`
 * `@Fhp\Rest\PropertyType\IntegerType`

### Defining custom entity-controllers

You may also define your own controllers to handle model-requests. Just pass
your controller's class name as the third parameter to `$api->createModel`.

Please extend `Fhp\Rest\Controller` and
 feel free to override the basic actions:

 * `public function indexAction($request, $response, $args) {}`
 * `public function showAction($request, $response, $args) {}`
 * `public function updateAction($request, $response, $args) {}`
 * `public function deleteAction($request, $response, $args) {}`
 * `public function createAction($request, $response, $args) {}`

by calling `$api->activateEntity` like following:

```php
$api->activateEntity(
    \My\Entity\Post::class,
    \My\Own\PostController::class
);
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
when going to production.

You may do this by following:

```php
$api->setHeaders([
    'Content-Type' => 'application/json',
    'Access-Control-Allow-Origin' => 'http://my-specific-doain.com'
]);
```

## Todos and issues

* PropertyTypes
* Refactoring
