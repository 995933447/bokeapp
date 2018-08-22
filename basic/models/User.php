<?php

namespace app\models;

use Yii;
use app\models\Userinfo;

/**
 * This is the model class for table "user".
 *
 * @property int $id 用户id
 * @property string $email
 * @property string $phone 注册手机
 * @property string $password
 * @property int $is_admin 1管理员，2非管理员
 * @property int $register_time
 * @property int $sex 1男2女
 * @property string $username
 * @property int $is_audit 是否通过审核。1通过，0不通过
 * @property int $is_lock 1警用，2不禁用 是否禁用
 * @property string $token 令牌
 * @property string $app_id app来源id
 */
class User extends \yii\db\ActiveRecord
{
    public $code;
    public $password_repeat;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    public function scenarios()
    {
        parent::scenarios();
        return [
            'app_register' => ['phone', 'username', 'password','app_id','code','password_repeat','register_time'],
            'app_login' => ['phone','username','password','app_id'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
           ['app_id','required','message' => '缺少app id','on' => 'app_register'],
           ['phone','required','message' => '请输入注册手机','on' => 'app_register'],
           ['username','required','message' => '请输入用户名','on' => 'app_register'],
           ['password','required','message' => '请输入密码','on' => 'app_register'],
           ['password','compare','compareAttribute' => 'password_repeat','message' => '两次密码输入不一致','on'=>'app_register'],
           ['register_time','safe','on' => 'register_time'],
           ['code','required','message' => '请输入手机验证码','on' => 'app_register'],
           [['phone','username','password','code','app_id'],'trim','on' => 'app_register'],
           [['username','phone'],'valiAccounter','on' => 'app_login','skipOnError' => true,'skipOnEmpty' => false],
           ['password','required','message' => '请输入密码','on' => 'app_login'],
           ['app_id','required','message' => '缺少app id','on' => 'app_login'],
           // [['token','expire'],'safe','on' => 'app_login']
        ];
    }

    public function valiAccounter($attribute,$params)
    {
       if($this->phone || $this->username) {
          return true;
       } else {
          $this->addError($attribute, '请输入用户名或者注册手机号');
          return false;
       }
    }

    public function getUserinfo()
    {
        return $this->hasOne(Userinfo::className(),['user_id' => 'id']);
    }

  
}
