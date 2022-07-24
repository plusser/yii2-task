<?php

namespace task\interfaces;

interface BrokerDriverInterface
{

    public function addQueueItem(string $queue, string $item): void;

    public function processQueue(string $queue, callable $callback, bool $requeue, int $processLimit): void;

}
