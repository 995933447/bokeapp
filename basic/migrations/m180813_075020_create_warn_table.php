<?php

use yii\db\Migration;
use yii\db\Schema;

/**
 * Handles the creation of table `warn`.
 */
class m180813_075020_create_warn_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('warn', [
            'id' => $this->primaryKey(),
            'warn_object' => Schema::TYPE_INTEGER,
            'addtime' => Schema::TYPE_INTEGER,
            'is_audit' => Schema::TYPE_TINYINT,//1已经审核，0未审核
            'type' =>  Schema::TYPE_TINYINT,//1动态，2视频，3照片,4用户
            'content' => Schema::TYPE_TEXT
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('warn');
    }
}
