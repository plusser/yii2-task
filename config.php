<?php

namespace task;

use task\components\Manager;
use task\drivers\RabbitMQDriver;

return [
    'manager' => [
        'class'         => Manager::class,
        'requeue'       => false,
        'processLimit'  => 10,
    ],
    'brokerDriver'  => [
        'class'     => RabbitMQDriver::class,
        'host'      => 'localhost',
        'port'      => 5672,
        'user'      => 'guest',
        'password'  => 'guest',
        'vhost'     => '/',
    ],
];
