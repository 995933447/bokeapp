<?php

use yii\db\Migration;
use yii\db\Schema;

/**
 * Handles the creation of table `collect`.
 */
class m180813_082224_create_collect_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('collect', [
            'id' => $this->primaryKey(),
            'user_id' => Schema::TYPE_INTEGER." COMMENT '操作用户id'",
            'comment_object' => Schema::TYPE_INTEGER." COMMENT '收藏对象'",
            'addtime' => Schema::TYPE_INTEGER." COMMENT '收藏时间'",
            'type' => Schema::TYPE_TINYINT." COMMENT '1动态，2照片，3视频'"
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('collect');
    }
}
