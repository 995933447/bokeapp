<?php

use yii\db\Migration;
use yii\db\Schema;

/**
 * Handles the creation of table `advence`.
 */
class m180813_082534_create_advence_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('advence', [
            'id' => $this->primaryKey(),
            'user_id' => Schema::TYPE_INTEGER." COMMENT '操作用户id'",
            'content' => Schema::TYPE_TEXT,
            'addtime' => Schema::TYPE_INTEGER,
            'is_audit' => Schema::TYPE_TINYINT."DEFAULT 1 COMMENT '1审核，0未审核'",
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('advence');
    }
}
