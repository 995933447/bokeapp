<?php

namespace app\models;

use Yii;
use app\models\Post;
use app\models\Photo;

/**
 * This is the model class for table "collect".
 *
 * @property int $id
 * @property int $user_id 操作用户id
 * @property int $comment_object 收藏对象
 * @property int $addtime 收藏时间
 * @property int $type 1动态，2照片，3视频
 */
class Collect extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'collect';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['collect_object' , 'type'], 'required'],
        ];
    }

    public function getPost()
    {
        return $this->hasOne(Post::className(),['id' => 'collect_object']);
    }

    public function getPhoto()
    {
        return $this->hasOne(Photo::className(),['id' => 'collect_object']);
    }
}
