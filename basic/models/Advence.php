<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "advence".
 *
 * @property int $id
 * @property int $user_id 操作用户id
 * @property string $content
 * @property int $addtime
 * @property int $is_audit 1审核，0未审核
 */
class Advence extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'advence';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['content'],'filter','filter' => 'htmlspecialchars'],
            [['content'],'filter','filter' => 'addslashes'],
        ];
    }

    
}
