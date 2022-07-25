<?php

namespace task\console;

use Yii;
use yii\helpers\Console;
use yii\console\Controller;
use yii\console\ExitCode;
use task\Module;
use Throwable;

class TaskController extends Controller
{

    public $queue;
    public $exchange;

    public function options($actionId): array
    {
        return [
            'queue',
            'exchange',
        ];
    }

    public function optionAliases(): array
    {
        return [
            'q' => 'queue',
            'e' => 'exchange',
        ];
    }

    public function actionRun(): int
    {
        $error = $this->checkTransferOptions($this->options($this->action->id));

        if(!$error){
            try{
                $this->singleRunning();
                Module::getInstance()->manager->processQueue((string) $this->queue, (string) $this->exchange);
            }catch(Throwable $e){
                $error = true;

                $this->stdout(PHP_EOL . $e->getMessage() . PHP_EOL, Console::FG_RED);
                $this->stdout(PHP_EOL . $e->getTraceAsString() . PHP_EOL, Console::FG_YELLOW);
            }
        }

        return $this->exitCode($error);
    }

    protected function exitCode(bool $error = false): int
    {
        return $error ? ExitCode::UNSPECIFIED_ERROR : ExitCode::OK;
    }

    protected function checkTransferOptions(array $options): bool
    {
        $error = false;

        foreach($options as $item){
            if(empty($this->{$item})){
                $this->notSpecifiedOption($item);
                $error = true;
            }
        }

        return $error;
    }

    protected function notSpecifiedOption(string $option): void
    {
        $this->stdout('Option not specified ', Console::BOLD);
        $this->stdout('--' . $option . (($key = array_search($option, $this->optionAliases())) ? ' (-' . $key . ')' : '') . PHP_EOL, Console::FG_RED);
    }

    protected function singleRunning()
    {
        if($this->checkPidFile()){
            exit(PHP_EOL . 'Already is running.' . PHP_EOL);
        }

        $this->savePidFile();
    }

    protected function getPidFilename(): string
    {
        return implode([
            Yii::$app->runtimePath,
            '/',
            $this->id,
            '_',
            $this->action->id,
            '_',
            $this->queue,
            '_',
            $this->exchange,
            '.pid',
        ]);
    }

    protected function checkPidFile(): bool
    {
        $result = false;

        if(file_exists($pidFilename = $this->getPidFilename())){
            $pid = (int) file_get_contents($pidFilename);
            if($pid > 0){
                $result = posix_kill($pid, 0);
            }
        }

        return $result;
    }

    protected function savePidFile(): bool
    {
        return (bool) file_put_contents($this->getPidFilename(), posix_getpid());
    }

}
