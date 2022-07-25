<?php

namespace task;

use Yii;
use yii\di\Instance;
use yii\helpers\ArrayHelper;

class Module extends \yii\base\Module
{

    public $controllerName = 'task';
    public $manager = [];
    public $brokerDriver = [];

    public function init()
    {
        parent::init();

        foreach(ArrayHelper::merge(require(__DIR__ . '/config.php'), [
            'manager'       => $this->manager,
            'brokerDriver'  => $this->brokerDriver,
        ]) as $componentName => $componentConfig){
            $this->{$componentName} = Instance::ensure($this->{$componentName} = $componentConfig, $this->{$componentName}['class']);
        }       

        if(Yii::$app instanceof yii\console\Application){
            Yii::$app->controllerMap[$this->controllerName] = 'task\console\TaskController';
        }
    }

}
