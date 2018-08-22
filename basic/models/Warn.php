<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "warn".
 *
 * @property int $id
 * @property int $warn_object
 * @property int $addtime
 * @property int $is_audit
 * @property int $type
 * @property int $user_id 用户ID
 * @property string $content 内容
 */
class Warn extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'warn';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['warn_object', 'type'], 'required'],
            [['content'], 'string'],
            [['content'], 'filter','filter' => 'htmlspecialchars'],
            [['content'], 'filter','filter' => 'addslashes'],
        ];
    }
}
