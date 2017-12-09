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

### User (client) registration
Request method: 
```
POST
```
End point: 
```
http://{hostname}/v1/clients
```

Sample request data
```json
{
  "name": "Name",
  "email": "email-address"
}
```

Sample response
```json
{
    "code": 200,
    "message": "OK",
    "payload": {
        "name": "Kwame Nkansah",
        "email": "kwamenkanssahs@nkansah.com",
        "token": "BF7A8AFECCF256D840A4AD633EE250A850BAF646",
        "is_blocked": false,
        "id": 21,
        "created_at": "2017-12-09T18:07:02+00:00",
        "updated_at": null
    }
}
```

``NOTE: ALL REQUESTS FROM THIS SECTION DOWN REQUIRES THAT YOU PROVIDE YOUR ACCESS TOKEN  FOR IDENTIFICATION. HEADER NAME IS "QUEUE-ACCESS-TOKEN"``

### Creating a project

Request method:
```
POST
```
End point:
```
http://{hostname}/v1/projects
```
Sample data
```json
{
  "name": "My awesome project",
  "description": "My project description"
}

```

Sample response
```json
{
    "code": 200,
    "message": "OK",
    "payload": {
        "client": {
            "name": "Kwame Nkansah",
            "email": "kwamenkanssahs@nkansah.com",
            "is_blocked": false,
            "id": 1,
            "created_at": "2017-10-26T22:34:57+00:00",
            "updated_at": null
        },
        "code": "4FFAC17FE5CE7603D77B126C93570DCB949CB408",
        "name": "My awesome project",
        "description": "My project description",
        "id": 21,
        "created_at": "2017-12-09T18:20:11+00:00",
        "updated_at": null
    }
}
```

### Listing projects
Request method:
```
GET
```

End point
```
http://{hostname}/v1/projects
```
You may provide a "limit" and an "offset" query parameters to paginate returned collection of projects. "limit" must be greater or equal to one and "offset" must be greater of equal to zero.

### Creating a job
Request method:
```
POST
```

End point
```
http://{hostname}/v1/projects/{project-code}/jobs
```

Sample request data
```json
{
  "title": "Latest job",
  "description": "Description for latest job",
  "retry_delay": 120, //Defaults to 1800 seconds (30 minutes)
  "callback_url": "http://www.google.com"
}
```

### Fetching jobs under a project
Request method:
```
GET
```

End point:
```
http://{hostname}/v1/projects/{project}/jobs
```

### Fetching a single job under a project
Request method:

```
GET
```

End point
```
http://{hostname}/v1/projects/{project-code}/jobs/{job-code}
```

### Viewing outputs for a job

Request method:
```
GET
```

End point:

```
http://{hostname}/v1/projects/{project-code}/jobs/{job-code}/outputs
```