<?php

namespace task\interfaces;

interface BrokerDriverInterface
{

    public function addQueueItem(string $queue, string $exchange, string $item): void;

    public function processQueue(string $queue, string $exchange, callable $callback, bool $requeue, int $processLimit): void;

}
