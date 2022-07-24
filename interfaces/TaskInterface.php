<?php

namespace task\interfaces;

interface TaskInterface
{

    public function run(array $data): bool;

}
