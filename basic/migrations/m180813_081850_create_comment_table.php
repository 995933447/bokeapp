<?php

use yii\db\Migration;
use yii\db\Schema;

/**
 * Handles the creation of table `comment`.
 */
class m180813_081850_create_comment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('comment', [
            'id' => $this->primaryKey(),
            'user_id' => Schema::TYPE_INTEGER." COMMENT '操作用户id'",
            'comment_object' => Schema::TYPE_INTEGER." COMMENT '评论对象'",
            'addtime' => Schema::TYPE_INTEGER." COMMENT '评论时间'",
            'type' => Schema::TYPE_TINYINT." COMMENT '1动态，2照片，3视频'",
            'content' => Schema::TYPE_TEXT
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('comment');
    }
}
