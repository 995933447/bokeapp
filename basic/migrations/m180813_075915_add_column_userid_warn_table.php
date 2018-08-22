<?php

use yii\db\Migration;
use yii\db\Schema;

/**
 * Class m180813_075915_add_column_userid_warn_table
 */
class m180813_075915_add_column_userid_warn_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180813_075915_add_column_userid_warn_table cannot be reverted.\n";

        return false;
    }

    
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $this->addColumn('warn', 'user_id', Schema::TYPE_INTEGER . ' COMMENT "用户ID"');
    }

    public function down()
    {
        echo "m180813_075915_add_column_userid_warn_table cannot be reverted.\n";

        return false;
    }
    
}
