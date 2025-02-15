<?php

use common\models\User;
use yii\db\Migration;

/**
 * Class m241224_140117_add_fields_to_users_table
 */
class m241224_140117_add_fields_to_users_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(User::tableName(), 'access_token', $this->string());
        $this->addColumn(User::tableName(), 'full_name', $this->string());
        $this->addColumn(User::tableName(),'cancelled_plan', $this->boolean()->defaultValue(0));
        $this->addColumn(User::tableName(), 'shopify_details', $this->text());
        $this->addColumn(User::tableName(), 'videos_checked', $this->boolean()->defaultValue(0));
        $this->addColumn(User::tableName(), 'fail_count', $this->tinyInteger()->defaultValue(0));
        $this->addColumn(User::tableName(), 'custom_plan_visible', $this->boolean()->defaultValue(0));
        $this->addColumn(User::tableName(), 'review_suggest_count', $this->smallInteger()->defaultValue(0));
        $this->addColumn(User::tableName(), 'review_suggested_at', $this->integer());
        $this->addColumn(User::tableName(), 'has_left_review', $this->integer());
        $this->addColumn(User::tableName(), 'left_review_at', $this->integer());

        if (!$this->db->getTableSchema(User::tableName())->getColumn('is_manual_plan')) {
            $this->addColumn(User::tableName(), 'is_manual_plan', $this->boolean()->defaultValue(0));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m241224_140117_add_fields_to_users_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m241224_140117_add_fields_to_users_table cannot be reverted.\n";

        return false;
    }
    */
}
