<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "comment".
 *
 * @property int $id
 * @property int $user_id 操作用户id
 * @property int $comment_object 点赞对象
 * @property int $addtime 点赞时间
 * @property int $type 1动态，2照片，3视频
 */
class Comment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'comment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['comment_object','type'],'required'],
            ['content','required','message' => '评论内容不能为空'],
            [['content'],'filter','filter' => 'htmlspecialchars'],
            [['content'],'filter','filter' => 'addslashes'],
        ];
    }

    public function getUser()
    {
         return $this->hasOne(User::className(),['id' => 'user_id']);
    }
}
