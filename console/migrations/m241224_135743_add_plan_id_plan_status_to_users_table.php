<?php

use common\models\User;
use yii\db\Migration;

/**
 * Class m241224_135743_add_plan_id_plan_status_to_users_table
 */
class m241224_135743_add_plan_id_plan_status_to_users_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(User::tableName(), 'plan_id', $this->integer()->defaultValue(null));
        $this->addColumn(User::tableName(), 'plan_status', $this->boolean()->defaultValue(0));

        $this->addForeignKey(
            'fk-user_plan_id',
            '{{%users}}',
            'plan_id',
            '{{%plans}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m241224_135743_add_plan_id_plan_status_to_users_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m241224_135743_add_plan_id_plan_status_to_users_table cannot be reverted.\n";

        return false;
    }
    */
}
