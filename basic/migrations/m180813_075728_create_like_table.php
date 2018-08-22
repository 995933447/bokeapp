<?php

use yii\db\Migration;
use yii\db\Schema;

/**
 * Handles the creation of table `like`.
 */
class m180813_075728_create_like_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('like', [
            'id' => $this->primaryKey(),
            'user_id' => Schema::TYPE_INTEGER." COMMENT '操作用户id'",
            'like_object' => Schema::TYPE_INTEGER." COMMENT '点赞对象'",
            'addtime' => Schema::TYPE_INTEGER." COMMENT '点赞时间'",
            'type' => Schema::TYPE_TINYINT." COMMENT '1动态，2照片，3视频'"
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('like');
    }
}
