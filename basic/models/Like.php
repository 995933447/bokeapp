<?php

namespace app\models;

use Yii;
use app\models\User;

/**
 * This is the model class for table "like".
 *
 * @property int $id
 * @property int $user_id 操作用户id
 * @property int $like_object 点赞对象
 * @property int $addtime 点赞时间
 * @property int $type 1动态，2照片，3视频
 */
class Like extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'like';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [[ 'like_object', 'type'], 'required'],
        ];
    }

   public function getUser()
   {
        return $this->hasOne(User::className(),['id' => 'user_id']);
   }


}
