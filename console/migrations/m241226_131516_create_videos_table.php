<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%videos}}`.
 */
class m241226_131516_create_videos_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';

        $this->createTable('{{%videos}}', [
            'id' => $this->primaryKey(),
            'title' => $this->text(),
            'description' => $this->text(),
            'video_url' => $this->text(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%videos}}');
    }
}
