<?php

namespace task\drivers;

use yii\base\Component;
use yii\di\Instance;
use yii\redis\Connection;
use task\interfaces\BrokerDriverInterface;
use Throwable, Exception;

class RedisDriver extends Component implements BrokerDriverInterface
{

    public $redis;

    public function init()
    {
        parent::init();
        $this->redis = Instance::ensure($this->redis, Connection::class);
    }

    public function addQueueItem(string $queue, string $item): void
    {
        $this->redis->rpush($queue, $item);
    }

    public function processQueue(string $queue, callable $callback, bool $requeue, int $processLimit): void
    {
        $processList = [];

        while(true){
            if(count($processList) < $processLimit){
                $message = $this->redis->lpop($queue);

                if(!is_null($message)){

                    $pid = pcntl_fork();

                    if($pid === -1){
                        throw new Exception('Fork child process failed.');
                    }elseif($pid){
                        $processList[$pid] = true;
                    }else{
                        try{
                            $callback($message);
                        }catch(Throwable $e){
                            if($requeue){
                                $this->addQueueItem($queue, $message);
                            }
                
                            throw $e;
                        }

                        exit;
                    }
                }
            }

            usleep(1000);

            while($pid = pcntl_waitpid(-1, $status, WNOHANG)){
                if($pid == -1){
                    $processList = [];
                    break;
                }else{
                    unset($processList[$pid]);
                }
            }
        }
    }

}
