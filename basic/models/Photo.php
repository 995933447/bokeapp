<?php

namespace app\models;

use Yii;
use app\models\User;

/**
 * This is the model class for table "photo".
 *
 * @property int $id 主键
 * @property string $photo 照片
 * @property int $user_uid 用户Id
 * @property int $post_id 动态Id
 * @property int $addtime 添加时间
 * @property int $updatetime 更新时间
 */
class Photo extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'photo';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['photo'], 'required', 'message' => '非法操作'],
        ];
    }

   public function getLike()
    {
        return $this->hasMany(Like::className(),['like_object' => 'id']);
    }

    public function getComment()
    {
        return $this->hasMany(Comment::className(),['comment_object' => 'id']);
    }
    
    public function getUser()
    {
        return $this->hasOne(User::className(),['id' => 'user_id']);
    }
}
