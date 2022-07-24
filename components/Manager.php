<?php

namespace task\components;

use yii\base\Component;
use task\interfaces\BrokerDriverInterface;
use task\interfaces\TaskInterface;
use task\Module;
use Exception;

class Manager extends Component
{

    public $requeue;
    public $processLimit;

    public function addTask(string $queue, string $taskClass, array $data = []): void
    {

        if(!in_array(TaskInterface::class, class_implements($taskClass))){
            throw new Exception('Class `' . $taskClass . '` not implements ' . TaskInterface::class);
        }

        $this->getBrokerDriver()->addQueueItem($queue, serialize((object) [
            'class' => $taskClass,
            'data'  => $data,
        ]));
    }

    public function processQueue(string $queue): void
    {
        $this->getBrokerDriver()->processQueue($queue, function($message){
            $messageData = unserialize($message);
            $runTask = function(TaskInterface $task, array $taskData): bool {return $task->run($taskData);};

            if(!$runTask(new $messageData->class, (array) $messageData->data)){
                throw new Exception('Task `' . $messageData->class . '` not completed.');
            }
        }, $this->requeue, $this->processLimit);
    }

    protected function getBrokerDriver(): BrokerDriverInterface
    {
        return Module::getInstance()->brokerDriver;
    }

}
