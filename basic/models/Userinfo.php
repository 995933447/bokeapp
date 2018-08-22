<?php

namespace app\models;

use Yii;

use yii\web\UploadedFile;


/**
 * This is the model class for table "userinfo".
 *
 * @property string $face ͷ��
 * @property int $id ����
 * @property string $intro ���˼��
 * @property int $area_id ����Id
 * @property int $user_id �û�id
 * @property int $addtime
 * @property int $updatetime ����ʱ��
 */
class Userinfo extends \yii\db\ActiveRecord
{
    public $imageFile;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'userinfo';
    }

    public function scenarios()
    {
        return [
            'default' => ['imageFile'],
            'setdata' =>['sex','intro','province','city','town','province_id','city_id','town_id'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'face' => 'Face',
            'id' => 'ID',
            'intro' => 'Intro',
            'area_id' => 'Area ID',
            'user_id' => 'User ID',
            'addtime' => 'Addtime',
            'updatetime' => 'Updatetime',
        ];
    }
    
    public function rules()
       {
           return [
               [['imageFile'], 'file', 'skipOnEmpty' => false,'extensions' => 'png,jpg,gif,jpep','maxFiles' => 1],
               [['intro'],'filter', 'filter' => 'htmlspecialchars','on' => 'setdata'],
               [['intro'],'filter', 'filter' => 'addslashes','on' => 'setdata'],
               ['sex','required','message' => '��ѡ���Ա�','on' => 'setdata'],
               ['sex','match','pattern' => '/^(1|2)$/','message' => '�Ƿ�����','on' => 'setdata'],
               ['province','required','message' => '��ѡ������ʡ��','on' => 'setdata'],
               ['city','required','message' => '��ѡ�����ڳ���','on' => 'setdata'],
               ['town','required','message' => '��ѡ����������','on' => 'setdata'],
               [['province_id','city_id','town_id'],'required','on' => 'setdata'],
           ];
       }
       
       public function upload()
       {
           if ($this->validate()) {
               $dir = 'uploads/face/'.date('Y-m-d');
               is_dir($dir) || mkdir($dir,0777,true);
               $path = $dir.'/'.uniqid().rand(0,1000).'.'.$this->imageFile->extension;
               $this->imageFile->saveAs($path);
               return $path;
           } else {
               return false;
           }
       }

}
