<?php
namespace app\controllers;
use Yii;
use app\controllers\CommonController;
use yii\web\UploadedFile;
use app\models\UploadForm;
use app\models\Post;
use app\models\Vedio;
use yii\helpers\Url;
use app\models\Photo;

class PostController extends CommonController
{

    private $count = 15;

    /**
       * showdoc
       * @title 上传动态图片
       * @description 上传动态图片的接口
       * @catalog api文档/动态相关
       * @method post
       * @url xxx/index.php?r=post/upload-photos
       * @param imageFile[] 必选 array 文件表单
       * @return {flag:true}
       * @return_param flag bool 操作结果表示。成功为true或1，失败为false或0
       * @return_param error array 错误信息
       * @remark 
       * @number 
       */
    public function actionUploadPhotos()
    {
       if(Yii::$app->request->isPost) {
       		 $res['flag'] = false;
       		 $model = new UploadForm;
       		 $this->formatFILES('UploadForm','imageFile');
             $model->imageFile = UploadedFile::getInstances($model, 'imageFile');
             if ($path = $model->uploadImgs()) {
               foreach ($path as $key => $value) {
               	 $path[$key] = $this->addFileBase($value);
               }
               $res['path'] = $path;
               $res['flag'] = true;
               return json_encode($res);
             }
        } else {
        	$res['error'] = $model->errors;
        	return json_encode($res);
        }
    }

    /**
       * showdoc
       * @title 上传动态视频
       * @description 上传动态视频的接口
       * @catalog api文档/动态相关
       * @method post
       * @url xxx/index.php?r=post/upload-video
       * @param vedioFile[] 必选 array 文件表单
       * @return {flag:true}
       * @return_param flag bool 操作结果表示。成功为true或1，失败为false或0
       * @return_param error array 错误信息
       * @remark 
       * @number 
       */
    public function actionUploadVideo() 
    {
    	if(	Yii::$app->request->isPost) {
    		$res['flag'] = false;
    		if(!isset($_FILES['vedioFile'])) {
    			$res['error'] = '没有文件被上传';
    			return json_encode($res);
    		}
    		$type = $_FILES['vedioFile']['type'][0];
    		switch ($type) {
    			case 'video/mp4':
    				$ok = 1;
    				break;
    			case 'video/ogg':
    				$ok = 1;
    				break;
    			case 'video/webm':
    				$ok = 1;
    				break;
    			default:
    				$ok = 0;
    				break;
    		}
    		if($ok) {
    			$model = new UploadForm;
    		    $this->formatFILES('UploadForm','vedioFile');
    			$model->vedioFile = UploadedFile::getInstances($model, 'vedioFile');
    			$model->vedioFile = $model->vedioFile[0];
    			if($path = $model->uploadVedio()) {
    				$path = $this->addFileBase($path);
                    $res['path'] = $path;
                    return json_encode($res);
    			} else {
    				$res['error'] = $model->errors;
    				return json_encode($res);
    			}
    		} else {
    			$res['error'] = '上传文件非视频格式';
    			return json_encode($res);
    		}
    	}
    } 

    /**
       * showdoc
       * @title 发布动态
       * @description 发布动态的接口
       * @catalog api文档/动态相关
       * @method post
       * @url xxx/index.php?r=post/send
       * @param  content 可选 string 动态内容
       * @param  photo 可选 string 图片地址
       * @param  vedio 可选 string 视频地址
       * @return {flag:true}
       * @return_param flag bool 操作结果表示。成功为true或1，失败为false或0
       * @return_param error array 错误信息
       * @remark content,photo,vedio参数其中一个必选
       * @number 
       */
    public function actionSend()
    {   
        $res['flag'] = false;
        $content = Yii::$app->request->getBodyParam('content');
        $photo = Yii::$app->request->getBodyParam('photo');
        $vedio = Yii::$app->request->getBodyParam('vedio');
        if(!isset($content) && !isset($photo) && !isset($vedio) && !trim($content) && !trim($photo) && !trim($vedio)) {
            $res['error'] = '请输入动态内容';
            return json_encode($res); 
        }
        if($photo && $vedio) {
            $res['error'] = '图片和视频同时只能上传一种';
            return json_encode($res);
        }
        if($photo) {
            if(!is_array($photo)) {
                $res['error'] = '非法操作';
                return json_encode($res);
            }
            foreach ($photo as $key => $value) {
                $photo[$key] = str_replace(Url::base(true).'/', '', $value);
                if(!file_exists($photo[$key])) {
                    $res['error'] = '图片文件损坏，请重新上传';
                    return json_encode($res);
                }
            }
        }
        if($vedio) {
            $vedio = str_replace(Url::base(true).'/','',$vedio);
            if(!file_exists($vedio)) {
                $res['error'] = '视频文件损坏，请重新上传';
                return json_encode($res);
            }
        }
        $model_post = new Post();
        $model_post->content = $content;
        if($model_post->validate()) {
           $model_post->content = addslashes(htmlspecialchars($model_post->content)); 
        }
        $model_post->user_id = \Yii::$app->request->getBodyParam('id');
        $model_post->addtime = time();
        $model_post->app_id = \Yii::$app->request->getBodyParam('app_id');
        $model_post->save(false);
        $new_post_id = $model_post->id;
        if($photo) {
            if(is_array($photo)) {
                $model_photo = new Photo();
                foreach ($photo as $key => $value) {
                    $model_photo->photo = $value;
                    if($model_photo->validate()) {
                        $user_id = Yii::$app->request->getBodyParam('id');
                        $model_photo->post_id = $new_post_id;
                        $model_photo->user_id = $user_id;
                        $model_photo->addtime = time();
                        $model_photo->app_id = \Yii::$app->request->getBodyParam('app_id');
                        $model_photo->save(false);
                    }
                }
            }
        }
        $model_vedio = new Vedio();
        $model_vedio->vedio = $vedio;
        if($model_vedio->validate()) {
            $model_vedio->post_id = $new_post_id;
            $model_vedio->user_id = Yii::$app->request->getBodyParam('id');
            $model_vedio->addtime = time();
            $model_vedio->save(false);
        }
        $res['flag'] = true;
        return json_encode($res);
    }

    /**
       * showdoc
       * @title 批量获取动态
       * @description 批量获取动态的接口
       * @catalog api文档/动态相关
       * @method post
       * @url xxx/index.php?r=post/get-data
       * @param  type 必选 int 获取动态类型，1为我的动态，2为所有人动态
       * @param  last_id 必选 int 上次下拉刷新最后一条动态id，第一次获取则为0 
       * @param  count 可选 int 动态一次获取的条数，默认为15条
       * @param  uid 可选 int 获取动态所属的用户id，只在type为2在时候有效
       * @return {flag:true,data:array}
       * @return_param flag bool 操作结果表示。成功为true或1，失败为false或0
       * @return_param error array 错误信息
       * @return_param data array 数据信息
       * @remark 
       * @number 
       */
    public function actionGetData()
    {
        $res['flag'] = false;
        if(!$type = Yii::$app->request->getBodyParam('type')) {
            $res['error'] = '缺少参数type';
            return json_encode($res);
        }
        if(!$last_id = Yii::$app->request->getBodyParam('last_id')) {
            $last_id = 0;
        } else {
            $last_id = $last_id;
        }
        if($count = Yii::$app->request->getBodyParam('count')) {
            $count = $count;
        } else {
            $count = $this->count;
        } 
        if(intval($type) == 1) {
            if($last_id) {
                $data = Post::find()->with(['photo','vedio','like.user.userinfo','comment.user.userinfo'])->where(['user_id' => Yii::$app->request->getBodyParam('id'),'is_del' => 0])->andWhere(['<','id',$last_id])->limit($count)->asArray()->orderBy('id DESC')->all();
            } else {
                $data = Post::find()->with(['photo' , 'vedio','like.user.userinfo','comment.user.userinfo'])->where(['user_id' => Yii::$app->request->getBodyParam('id'),'is_del' => 0])->limit($count)->orderBy('id DESC')->asArray()->all();
            }
        } elseif(intval($type) == 2) {
            if($last_id) {
                if(!$uid = Yii::$app->request->getBodyParam('user_id')) {
                    $data = Post::find()->with(['photo','vedio','like.user.userinfo','comment.user.userinfo','user.userinfo'])->where(['app_id' => Yii::$app->request->getBodyParam('app_id'),'is_del' => 0])->andWhere(['<','id',$last_id])->limit($count)->asArray()->orderBy('id DESC')->all();
                } else {
                    $data = Post::find()->with(['photo','vedio','like.user.userinfo','comment.user.userinfo','user.userinfo'])->where(['app_id' => Yii::$app->request->getBodyParam('app_id'),'user_id' => $uid,'is_del' => 0])->andWhere(['<','id',$last_id])->limit($count)->asArray()->orderBy('id DESC')->all();
                }
            } else {
                if(!$uid = Yii::$app->request->getBodyParam('user_id')) {
                    $data = Post::find()->with(['photo' , 'vedio','like.user.userinfo','comment.user.userinfo','user.userinfo'])->where(['app_id' => Yii::$app->request->getBodyParam('app_id'),'is_del' => 0])->limit($count)->orderBy('id DESC')->asArray()->all();
                } else {
                    $data = Post::find()->with(['photo' , 'vedio','like.user.userinfo','comment.user.userinfo','user.userinfo'])->where(['app_id' => Yii::$app->request->getBodyParam('app_id'),'user_id' => $uid,'is_del' => 0])->limit($count)->orderBy('id DESC')->asArray()->all();
                }
            }
        } else {
            $res['error'] = '非法请求';
            return json_encode($res);
        }
        $data_output = [];
        if($data) {
            foreach ($data as $key => $value) {
                $data_output[$key]['add_date'] = date('Y-m-d H:i:s',$value['addtime']);
                if($value['updatetime']) $data_output[$key]['upate_date'] = date('Y-m-d H:i:s',$value['updatetime']);
                $data_output[$key]['content'] = $value['content'];
                $data_output[$key]['post_id'] = $value['id'];
                $data_output[$key]['addtime'] = $value['addtime'];
                $data_output[$key]['updatetime'] = $value['updatetime'];
                if(isset($value['user'])) {
                    $data_output[$key]['username'] = $value['user']['username'];
                    $data_output[$key]['user_id'] = $value['user']['id'];
                    if($value['user']['userinfo'] && $value['user']['userinfo']['face']) $data_output[$key]['face'] = $this->addFileBase($value['user']['userinfo']['face']);
                }
                if($value['photo']) {
                    foreach ($value['photo'] as $k => $v) {
                        $data_output[$key]['photo'][$k] = $this->addFileBase($v['photo']);
                    }
                }
                if($value['vedio']) {
                    $data_output[$key]['vedio'] = $this->addFileBase($value['vedio']['vedio']);
                }
                if($value['like']) {
                    foreach($value['like'] as $k2 => $v2) {
                        $data_output[$key]['like'][$k2] = [
                           'addtime' => $v2['addtime'],
                           'add_date' => date('Y-m-d H:i:s',$v2['addtime']),
                           'user_id' => $v2['user']['id'],
                           'username' => $v2['user']['username'],
                        ];
                        if($v2['user']['userinfo'] && $v2['user']['userinfo']['face']) {
                            $data_output[$key]['like'][$k2]['face'] = $this->addFileBase($v2['user']['userinfo']['face']);
                        } else {
                            $data_output[$key]['like'][$k2]['face'] = null;
                        }
                    } 
                }
                if($value['comment']) {
                    foreach($value['comment'] as $k3 => $v3) {
                        $data_output[$key]['comment'][$k3] = [
                           'addtime' => $v3['addtime'],
                           'add_date' => date('Y-m-d H:i:s',$v3['addtime']),
                           'content' => $v3['content'],
                           'user_id' => $v3['user']['id'],
                           'username' => $v3['user']['username'],
                        ];
                        if($v3['user']['userinfo'] && $v3['user']['userinfo']['face']) {
                            $data_output[$key]['comment'][$k3]['face'] = $this->addFileBase($v3['user']['userinfo']['face']);
                        } else {
                            $data_output[$key]['comment'][$k3]['face'] = null;
                        }
                    }
                }
            }
        }
        $res['flag'] = true;
        $res['data'] = $data_output;
        return json_encode($res);
    }

    /**
       * showdoc
       * @title 真永久删除动态
       * @description 真永久删除动态的接口
       * @catalog api文档/动态相关
       * @method post
       * @url xxx/index.php?r=post/del
       * @param  post_id 必选 int 动态id
       * @return {flag:true}
       * @return_param flag bool 操作结果表示。成功为true或1，失败为false或0
       * @return_param error array 错误信息
       * @remark 
       * @number 
       */
    public function actionDel()
    {
        $res['flag'] = false;
        if(!Yii::$app->request->getBodyParam('post_id')) {
            $res['error'] = '缺少参数post_id';
        }
        $post = Post::find()->with('photo','vedio','like','comment')->where(['user_id' => Yii::$app->request->getBodyParam('id'),'id' => Yii::$app->request->getBodyParam('post_id')])->one();
        if(!$post) {
            $res['error'] = '该动态不存在';
            return json_encode($res); 
        }
        foreach ($post['photo'] as $key => $value) {
           $post->unlink('photo',$value,true);
        }
        if($post['vedio']) {
            $post->unlink('vedio',$post->post['vedio'],true);
        }
        $post->delete();
        $res['flag'] = true;
        return json_encode($res);
    }

    /**
       * showdoc
       * @title 软删除动态
       * @description 软删除动态的接口
       * @catalog api文档/动态相关
       * @method post
       * @url xxx/index.php?r=post/soft-del
       * @param  post_id 必选 int 动态id
       * @return {flag:true}
       * @return_param flag bool 操作结果表示。成功为true或1，失败为false或0
       * @return_param error array 错误信息
       * @remark 
       * @number 
       */
    public function actionSoftDel()
    {
        $res['flag'] = false;
        if(!Yii::$app->request->getBodyParam('post_id')) {
            $res['error'] = '缺少参数post_id';
        }
        $post = Post::find()->with('photo','vedio','like','comment')->where(['user_id' => Yii::$app->request->getBodyParam('id'),'id' => Yii::$app->request->getBodyParam('post_id'),'is_del' => 0])->one();
        if(!$post) {
            $res['error'] = '该动态不存在';
            return json_encode($res); 
        }
        $post->is_del = 1;
        $post->save(false);
        $res['flag'] = true;
        return json_encode($res);
    }

    /**
       * showdoc
       * @title 查找某条动态
       * @description 查找某条动态的接口
       * @catalog api文档/动态相关
       * @method post
       * @url xxx/index.php?r=post/find-one
       * @param  post_id 必选 int 动态id
       * @return {flag:true,data:array}
       * @return_param flag bool 操作结果表示。成功为true或1，失败为false或0
       * @return_param error array 错误信息
       * @return_param data array 数据信息
       * @remark 
       * @number 
       */
    public function actionFindOne()
    {
        $res['flag'] = false;
        $post_id = Yii::$app->request->getBodyParam('post_id');
        if(!$post_id) {
            $res['error'] = '缺少参数post_id';
            return json_decode($res);
        }
        $data = Post::find()->with(['photo','vedio','like.user.userinfo','comment.user.userinfo'])->where(['user_id' => Yii::$app->request->getBodyParam('id'),'is_del' => 0])->andWhere(['id' => $post_id])->asArray()->one();
        if(!$data) {
            $res['error'] = '动态已被删除';
            return json_encode($res);
        }
        $data_output = [];
        $data_output['add_date'] = date('Y-m-d H:i:s',$data['addtime']);
        if($data['updatetime']) $data_output['upate_date'] = date('Y-m-d H:i:s',$data['updatetime']);
        $data_output['content'] = $data['content'];
        $data_output['post_id'] = $data['id'];
        $data_output['addtime'] = $data['addtime'];
        $data_output['updatetime'] = $data['updatetime'];
        if($data['photo']) {
            foreach ($data['photo'] as $k => $v) {
                $data_output['photo'][$k] = $this->addFileBase($v['photo']);
            }
        }
        if($data['vedio']) {
            $data_output['vedio'] = $this->addFileBase($data['vedio']['vedio']);
        }
        if($data['like']) {
            foreach($data['like'] as $k2 => $v2) {
                $data_output['like'][$k2] = [
                   'addtime' => $v2['addtime'],
                   'add_date' => date('Y-m-d H:i:s',$v2['addtime']),
                   'user_id' => $v2['user']['id'],
                   'username' => $v2['user']['username'],
                ];
                if($v2['user']['userinfo'] && $v2['user']['userinfo']['face']) {
                    $data_output['like'][$k2]['face'] = $this->addFileBase($v2['user']['userinfo']['face']);
                } else {
                    $data_output['like'][$k2]['face'] = null;
                }
            } 
        }
        if($data['comment']) {
            foreach($data['comment'] as $k3 => $v3) {
                $data_output['comment'][$k3] = [
                   'addtime' => $v3['addtime'],
                   'add_date' => date('Y-m-d H:i:s',$v3['addtime']),
                   'content' => $v3['content'],
                   'user_id' => $v3['user']['id'],
                   'username' => $v3['user']['username'],
                ];
                if($v3['user']['userinfo'] && $v3['user']['userinfo']['face']) {
                    $data_output['comment'][$k3]['face'] = $this->addFileBase($v3['user']['userinfo']['face']);
                } else {
                    $data_output['comment'][$k3]['face'] = null;
                }
            }
        }
        $res['flag'] = true;
        $res['data'] = $data_output;
        return json_encode($res);
    }

}
