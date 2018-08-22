<?php

namespace app\models;

use Yii;

use app\models\Photo;

use app\models\Vedio;

use app\models\Like;

use app\models\User;

/**
 * This is the model class for table "post".
 *
 * @property int $id 动态id
 * @property string $title 标题
 * @property int $addtime 添加时间
 * @property int $updatetime 更新时间
 * @property string $content 内容
 * @property int $user_id 用户id1
 * @property int $is_del 是否软删除，0否，1是
 * @property int $is_audit 是否审核通过，1是，0否
 */
class Post extends \yii\db\ActiveRecord
{

    // public $addDate;

    // public $updateDate;

    public function getAddDate()
    {
        return date('Y-m-d H:i:s',$this->addtime);
    }

    public function getUpdateDate()
    {
        if($this->updatetime) {
            return date('Y-m-d H:i:s',$this->updatetime);
        } else {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'post';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['content'], 'string'],
            [['content'],'required','message' => '动态内容不能为空'],
        ];
    }

    public function getPhoto()
    {
        return $this->hasMany(Photo::className(),['post_id' => 'id']);
    }

    public function getVedio()
    {
        return $this->hasOne(Vedio::className(),['post_id' => 'id']);
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
