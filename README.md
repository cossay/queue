# A Simple Queue Server

## Installation
Add the JSON below to your composer.json file and run ```composer update``` to install and update your dependencies.
```json
{
    "repositories": [
        {
            "type": "vcs",
            "url":  "https://github.com/cossay/queue"
        }
    ],
    "require": {
        "cosman/queue": "dev-master"
    }
}

```
## Database setup
Import the included SQL dump into your database.

## Setting up the front facing API server
Create a directory in the same folder as your composer.json file. This folder will serve as the document root your queue server.
Create an index.php file in the folder you just created and include the following lines of code.

```php
<?php
declare(strict_types = 1);
use Cosman\Queue\QueueServer;
use Illuminate\Database\Capsule\Manager;
require_once '../vendor/autoload.php';

$capsule = new Manager();

$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'queues',
    'username' => 'database-username',
    'password' => 'database-password',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => ''
]);

$capsule->setAsGlobal();
$queueServer = new QueueServer($capsule);

$queueServer->run();
```

## Setting up the task runner
