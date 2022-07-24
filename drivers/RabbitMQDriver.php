<?php

namespace task\drivers;

use yii\base\Component;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use task\interfaces\BrokerDriverInterface;
use Throwable, Exception;

class RabbitMQDriver extends Component implements BrokerDriverInterface
{
    
    public $host;
    public $port;
    public $user;
    public $password;
    public $vhost;

    protected $connection;
    protected $channel;

    public function init()
    {
        parent::init();

        $this->connection = new AMQPStreamConnection($this->host, $this->port, $this->user, $this->password, $this->vhost);
        $this->channel = $this->connection->channel();
    }

    public function __destruct()
    {
        $this->channel && $this->channel->close();
        $this->connection && $this->connection->close();
    }

    public function addQueueItem(string $queue, string $item): void
    {
        $this->channel->queue_declare($queue, false, true, false, false);
        $this->channel->basic_publish(new AMQPMessage($item, [
            'content_type'  => 'text/plain',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ]), '', $queue);
    }

    public function processQueue(string $queue, callable $callback, bool $requeue, int $processLimit): void
    {
        $consumer = function(AMQPMessage $message) use ($callback, $requeue): void{
            try{
                $callback($message->body);

                $message->ack();
            }catch(Throwable $e){
                $message->nack($requeue, true);
    
                throw $e;
            }

            $message->getChannel()->basic_cancel($message->getConsumerTag());
            exit;
        };

        $processList = [];

        while(true){
            if(count($processList) < $processLimit){
                $pid = pcntl_fork();

                if($pid === -1){
                    throw new Exception('Fork child process failed.');
                }elseif($pid){
                    $processList[$pid] = true;
                }else{
                    $this->init();
                    $this->channel->basic_qos(null, 1, null);
                    $this->channel->queue_declare($queue, false, true, false, false);
                    $this->channel->basic_consume($queue, 'PHP_' . posix_getpid(), false, false, false, false, $consumer);
                    $this->channel->consume();
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
