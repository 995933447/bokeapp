<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "vedio".
 *
 * @property int $id 主键
 * @property string $vedio 视频地址
 * @property int $user_id 用户id
 * @property int $post_id 动态id
 * @property int $addtime 添加时间
 * @property int $updatetime 更新时间
 */
class Vedio extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vedio';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['vedio'], 'required', 'message' => '非法操作'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'vedio' => 'Vedio',
            'user_id' => 'User ID',
            'post_id' => 'Post ID',
            'addtime' => 'Addtime',
            'updatetime' => 'Updatetime',
        ];
    }
}
