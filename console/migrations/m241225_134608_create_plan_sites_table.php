<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%plan_sites}}`.
 */
class m241225_134608_create_plan_sites_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';

        $this->createTable('{{%plan_sites}}', [
            'id' => $this->primaryKey(),
            'plan_id' => $this->integer(),
            'site_id' => $this->integer(),
        ], $tableOptions);

        $this->addForeignKey(
            'fk-plan-id-site',
            '{{%plan_sites}}',
            'plan_id',
            '{{%plans}}',
            '{{id}}',
            'CASCADE'

        );
        $this->addForeignKey(
            'fk-plan-site-id',
            '{{%plan_sites}}',
            'site_id',
            '{{%sites}}',
            '{{id}}',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%plan_sites}}');
    }
}
