<?php 
namespace app\controllers;
use Yii;
use app\models\User;
use app\models\Userinfo;
use app\controllers\CommonController;
use yii\web\UploadedFile;
use yii\helpers\Url;
use yii\base\DynamicModel;


class UserinfoController extends CommonController
{

    /**
        * showdoc
        * @title 上传头像
        * @description 上传头像的接口
        * @catalog api文档/用户资料
        * @method post
        * @url xxx/index.php?r=userinfo/upload-face
        * @param imageFile 必选 file  图片文件表单  
        * @return {flag:true}
        * @return_param flag bool 操作结果表示。成功为true或1，失败为false或0
        * @return_param error array 错误信息
        * @remark 
        * @number 
        */
      public function actionUploadFace()
      {
           $res['flag'] = false;
           $model = new Userinfo();
           if (Yii::$app->request->isPost) {
               $this->formatFILES('Userinfo','imageFile');
               $model->imageFile = UploadedFile::getInstance($model, 'imageFile');
               if ($path = $model->upload()) {
                   if($model->find()->where(['user_id' => Yii::$app->request->getBodyParam('id')])->one()) {
                        $model->updateAll(['face' => $path,'updatetime' => time()],['user_id' => Yii::$app->request->getBodyParam('id')]);
                   } else {
                        $model->user_id = Yii::$app->request->getBodyParam('id');
                        $model->face = $path;
                        $model->addtime = time();
                        $model->save(false);
                   }
                   $res['flag'] = true;
                   $res['path'] = $this->addFileBase($path);
                   return json_encode($res);
               } else {
                  $res['error'] = $model->errors;
                  return json_encode($res);
               }
           }
       } 

      /**
          * showdoc
          * @title 修改密码
          * @description 修改密码的接口
          * @catalog api文档/用户资料
          * @method post
          * @url xxx/index.php?r=userinfo/set-password
          * @param old_password 必选 string 原密码  
          * @param password 必选 string 新密码
          * @param password_repeat 必选 string 确认新密码
          * @return {flag:true}
          * @return_param flag bool 操作结果表示。成功为true或1，失败为false或0
          * @return_param error array 错误信息
          * @remark 
          * @number 
          */
       public function actionSetPassword()
       {
            $res['flag'] = false;
            $old_password = Yii::$app->request->getBodyParam('old_password');
            $password = Yii::$app->request->getBodyParam('password');
            $password_repeat = Yii::$app->request->getBodyParam('password_repeat');
            $validator = DynamicModel::validateData(compact('old_password','password','password_repeat'),[
               [['old_password'],'required','message' => '请输入原密码'],
               ['password','required','message' => '请输入新密码'],
               ['password','compare','compareAttribute' => 'password_repeat','message' => '两次密码输入不一致']
            ]);
            if($validator->hasErrors()) {
               $res['error'] = $validator->errors;
               return json_encode($res);
            }
            $model = new User();
            $request = Yii::$app->request;
            if(!$user_data = $model->find()->where(['id' => $request->getBodyParam('id'),'app_id' => $request->getBodyParam('app_id'),'password' => md5($request->getBodyParam('old_password'))])->one()) {
               $res['error'] = '原密码不正确';
               return json_encode($res);
            } else {
               $user_data->password = md5($request->getBodyParam('old_password'));
               $user_data->save(false);
               $res['flag'] = true;
               return json_encode($res);
            }
       }

     /**
        * showdoc
        * @title 修改资料
        * @description 修改资料的接口
        * @catalog api文档/用户资料
        * @method post
        * @url xxx/index.php?r=userinfo/set-data
        * @param sex 必选 int 性别  
        * @param intro 可选 string 个人简介
        * @param province 必选 string 省份
        * @param city 必选 string 城市  
        * @param town 必选 string 市镇
        * @param province_id 必选 int 省份id
        * @param city_id 必选 int 城市id
        * @param town_id 必选 int 市镇id
        * @return {flag:true}
        * @return_param flag bool 操作结果表示。成功为true或1，失败为false或0
        * @return_param error array 错误信息
        * @remark 
        * @number 
        */
       public function actionSetData()
       {
            $res['flag'] = false;
            $data = Yii::$app->request->bodyParams;
            $model = new Userinfo();
            $model->setScenario('setdata');
            $model->attributes = $data;
            if(!$model->validate()) {
               $res['error'] = $model->errors;
               return json_encode($res);
            }
            $userinfo = $model->findOne(['user_id' => $data['id']]);
            $userinfo->setScenario('setdata');
            $userinfo->attributes = $data;
            $userinfo->updatetime = time();
            $userinfo->save(false);
            $res['flag'] = true;
            return json_encode($res); 
       }

      /**
        * showdoc
        * @title 获取用户数据
        * @description 获取用户数据的接口
        * @catalog api文档/用户资料
        * @method post
        * @url xxx/index.php?r=userinfo/get-data
        * @return {flag:true}
        * @return_param flag bool 操作结果表示。成功为true或1，失败为false或0
        * @return_param error array 错误信息
        * @remark 
        * @number 
        */
       public function actionGetData()
       {
            $res['flag'] = false;
            $data = User::findOne(Yii::$app->request->getBodyParam('id'));
            $userinfo = $data->userinfo;
            if($userinfo) {
               $userinfo = $userinfo->toArray();
               $userinfo['face'] = $this->addFileBase($userinfo['face']);
               unset($userinfo['id']);
               unset($userinfo['user_id']);
            } else {
               $userinfo = [];
            }
            $userinfo['username'] = $data->username;
            $res['flag'] = true;
            $res['data'] = $userinfo;
            return json_encode($res);
       }
    
}


 ?>