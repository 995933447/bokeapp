<?php

namespace app\controllers;
use Yii;
use app\controllers\CommonController;
use app\models\Photo;

class PhotoController extends CommonController
{
	private $model;

	private $count = 15;

	public function init()
	{
		parent::init();
		$this->model = new Photo();
	}

    /**
       * showdoc
       * @title 排量获取照片
       * @description 排量获取照片的接口
       * @catalog api文档/相册相关
       * @method post
       * @url xxx/index.php?r=photo/get-photos
       * @param  type 必选 int 获取类型，1为本人照片，2为他人照片
       * @param  last_id 必选 int 上次下拉刷新最后一条照片id，第一次刷新则为0
       * @param  count 可选 int 获取照片条数
       * @param  user_id 可选 int 照片的用户id，type为2时必需
       * @return {flag:true}
       * @return_param flag bool 操作结果表示。成功为true或1，失败为false或0
       * @return_param error array 错误信息
       * @remark 
       * @number 
       */
    public function actionGetPhotos()
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
                $data = Photo::find()->with(['like.user.userinfo','comment.user.userinfo'])->where(['user_id' => Yii::$app->request->getBodyParam('id')])->andWhere(['<','id',$last_id])->limit($count)->asArray()->orderBy('id DESC')->all();
            } else {
                $data = Photo::find()->with(['like.user.userinfo','comment.user.userinfo'])->where(['user_id' => Yii::$app->request->getBodyParam('id')])->limit($count)->orderBy('id DESC')->asArray()->all();
            }
        } elseif(intval($type) == 2) {
            if($uid = Yii::$app->request->getBodyParam('user_id')) {
                if($last_id) {
                    $data = Photo::find()->with(['like.user.userinfo','comment.user.userinfo'])->where(['app_id' => Yii::$app->request->getBodyParam('app_id'),'user_id' => $uid])->andWhere(['<','id',$last_id])->limit($count)->asArray()->orderBy('id DESC')->all();
                } else {
                    $data = Photo::find()->with(['like.user.userinfo','comment.user.userinfo'])->where(['app_id' => Yii::$app->request->getBodyParam('app_id'),'user_id' => $uid])->limit($count)->orderBy('id DESC')->asArray()->all();
                }
            } else {
                $res['error'] = '非法操作';
                return json_encode($res);
            }
        } else {
            $res['error'] = '非法操作';
            return json_encode($res);
        }
     	
     	$data_output = [];
        if($data) {
        	foreach ($data as $key => $value) {
        	    $data_output[$key]['add_date'] = date('Y-m-d H:i:s',$value['addtime']);
        	    if($value['updatetime']) $data_output[$key]['upate_date'] = date('Y-m-d H:i:s',$value['updatetime']);
        	    $data_output[$key]['photo'] = $this->addFileBase($value['photo']);
        	    $data_output[$key]['photo_id'] = $value['id'];
        	    $data_output[$key]['addtime'] = $value['addtime'];
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
       * @title 获取单张照片
       * @description 获取单张照片的接口
       * @catalog api文档/相册相关
       * @method post
       * @url xxx/index.php?r=photo/find-one
       * @param  photo_id 必选 int 照片id
       * @return {flag:true}
       * @return_param flag bool 操作结果表示。成功为true或1，失败为false或0
       * @return_param error array 错误信息
       * @remark 
       * @number 
       */
    public function actionFindOne()
    {
        $res['flag'] = false;
        $photo_id = Yii::$app->request->getBodyParam('photo_id');
        if(!$photo_id) {
            $res['error'] = '缺少参数photo_id';
            return json_encode($res);
        }
        $data = Photo::find()->with(['like.user.userinfo','comment.user.userinfo'])->where(['user_id' => Yii::$app->request->getBodyParam('id')])->andWhere(['id' => $photo_id])->asArray()->one();
        if(!$data) {
            $res['error'] = '照片已被删除';
            return json_decode($res);
        }
        $data_output['add_date'] = date('Y-m-d H:i:s',$data['addtime']);
        if($data['updatetime']) $data_output['upate_date'] = date('Y-m-d H:i:s',$data['updatetime']);
        $data_output['photo'] = $this->addFileBase($data['photo']);
        $data_output['photo_id'] = $data['id'];
        $data_output['addtime'] = $data['addtime'];
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
