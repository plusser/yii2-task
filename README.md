Task
====
Console task`s wrap for Redis and RabbitMQ queues.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist plusser/yii2-task "*"
```

or add

```
"plusser/yii2-task": "*"
```

to the require section of your `composer.json` file.

Simple configuration:

1. Add Task module to config.

```
[
  ...
    'bootstrap' => [ ..., 'task', ]
    'modules'   => [
      'task'  => [
        'class'         => 'task\Module',
        'manager'       => [
            'requeue'       => false,
            'processLimit'  => 10,
        ],
        'brokerDriver'  => [
            'class'     => task\drivers\RabbitMQDriver::class,
            'host'      => 'localhost',
            'port'      => 5672,
            'user'      => 'guest',
            'password'  => 'guest',
            'vhost'     => '/',
        ],
        /*
        'brokerDriver' => [
            'class' => task\drivers\RedisDriver::class,
            'redis' => [
                'hostname'  => 'redis',
            ],
        ],
        */
      ],
    ],
  ...
]
```
2. Run code:

```
Yii::$app->getModule('task')->manager->addTask('task', 'exchange', \task\tasks\TestTask::class, [
  'message' => 'It`s a test.',
]);

```
3. Run command:

```
yii task/run -q task -e exchange
```
