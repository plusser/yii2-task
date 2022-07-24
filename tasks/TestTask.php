<?php

namespace task\tasks;

use task\interfaces\TaskInterface;

class TestTask implements TaskInterface
{

    public function run(array $data): bool
    {
        echo PHP_EOL . print_r($data, true) . PHP_EOL;

        return true;
    }

}
