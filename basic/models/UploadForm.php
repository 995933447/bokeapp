<?php
namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;

class UploadForm extends Model
{
    /**
     * @var UploadedFile
     */
    public $imageFile;

    public $vedioFile;
    
    public function scenarios()
    {
        return [
            'default' => ['imageFile'],
            'uploadvideo' => ['vediofile']
        ];
    }

    public function rules()
    {
        return [
            [['imageFile'], 'file','maxFiles' => 12,'skipOnEmpty' => false,'extensions' => 'png,jpg,gif,jpep'],
            [['vedioFile'],'file','maxFiles'=>1,'skipOnEmpty' => false,'on' => 'uploadvideo']
        ];
    }
    
    public function uploadImgs()
    {
        if ($this->validate()) { 
             $dir = 'uploads/photo/'.date('Y-m-d');
             is_dir($dir) || mkdir($dir,0777,true);
             $paths = [];
             foreach ($this->imageFile as $file) {
               $path = $dir.'/'.uniqid().rand(0,1000).'.'.$file->extension;
               $file->saveAs($path);
               $paths[] = $path;
             }
             return $paths;
        } else {
             return false;
        }
    }

    public function uploadVedio()
    {
       $this->setScenario('uploadvideo');
       if ($this->validate()) { 
            $dir = 'uploads/video/'.date('Y-m-d');
            is_dir($dir) || mkdir($dir,0777,true);
             $path = $dir.'/'.uniqid().rand(0,1000).'.'.$this->vedioFile->extension;
            $this->vedioFile->saveAs($path);
            return $path;
       } else {
            return false;
       } 
    }
}