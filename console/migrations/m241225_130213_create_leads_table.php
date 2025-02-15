<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%leads}}`.
 */
class m241225_130213_create_leads_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';

        $this->createTable('{{%leads}}', [
            'id' => $this->primaryKey(),
            'subject_id' => $this->integer(),
            'message' => $this->text(),
            'image' => $this->text(),
            'user_id' => $this->integer(),
            'status' => $this->boolean(),
            'additional_data' => $this->text(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $tableOptions);

        $this->addForeignKey(
            'fk-lead-subject-id',
            '{{%leads}}',
            'subject_id',
            '{{%subjects}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-lead-user-id',
            '{{%leads}}',
            'user_id',
            '{{%users}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%leads}}');
    }
}
