<?php


namespace console\jobs;


use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

class PublishJob extends BaseObject implements JobInterface
{

    public $id;

    /**
     * @param Queue $queue which pushed and is handling the job
     * @return void|mixed result of the job execution
     */
    public function execute($queue)
    {
        $yii = Yii::getAlias('@root').'/yii';
        shell_exec("php {$yii} product/publish {$this->id}");
    }
}