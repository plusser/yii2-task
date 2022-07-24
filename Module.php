<?php

namespace task;

use Yii;
use yii\helpers\ArrayHelper;

class Module extends \yii\base\Module
{

    public $controllerName = 'task';

    public function init()
    {
        parent::init();

        Yii::configure($this, ArrayHelper::merge(require(__DIR__ . '/config.php'), [
            'components' => $this->components,
        ]));
        

        if(Yii::$app instanceof yii\console\Application){
            Yii::$app->controllerMap[$this->controllerName] = 'task\console\TaskController';
        }
    }

}
