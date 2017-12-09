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
Create a directory in the same directory as your composer.json file. This directory will serve as the document root your front facing queue API server.
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
    'username' => 'root',
    'password' => 'cossay',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => ''
]);

$queueServer = new QueueServer($capsule->getConnection());

$queueServer->run();
```

## Setting up the job/task runner

Create a new php file outside your document root and place the following lines of into it.

```php
<?php
declare(strict_types = 1);
use Cosman\Queue\Runner\JobRunner;
use Cosman\Queue\Store\Repository\TaskRepository;
use GuzzleHttp\Client;
use Illuminate\Database\Capsule\Manager;
require_once './vendor/autoload.php';

$capsule = new Manager();

$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'queue-service-database-name',
    'username' => 'username',
    'password' => 'password',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => ''
]);

$repository = new TaskRepository($capsule->getConnection());

$httpClient = new Client(); // Customize this a much as you'd like

$runner = new JobRunner($httpClient, $repository);

$runner->run();
```
Launch the task runner script on the command line wifh the following command.
```
php -f path-to-runner-script-file.php
```

## API Server end points
