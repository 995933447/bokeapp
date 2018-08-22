<?php

namespace app\controllers;
use app\controllers\CommonController;
use app\models\Like;
use app\models\User;
use app\models\Post;
use app\models\Photo;
use Yii;

class LikeController extends CommonController
{
	private $model;

	public function init()
	{
		parent::init();
		$this->model = new Like();
	}

	/**
	   * showdoc
	   * @title 点赞
	   * @description 点赞的接口
	   * @catalog api文档/点赞相关
	   * @method post
	   * @url xxx/index.php?r=like
	   * @param  type 必选 int 点赞类型 1为动态，2为照片
	   * @param  like_object 必选 int 点赞的可以数据id，post_id或photo_id
	   * @return {flag:true}
	   * @return_param flag bool 操作结果表示。成功为true或1，失败为false或0
	   * @return_param error array 错误信息
	   * @remark 
	   * @number 
	   */
    public function actionIndex()
    {
       $res['flag'] = false;
       $this->model->attributes = Yii::$app->request->bodyParams;
       if($this->model->validate()) {
       		$type = Yii::$app->request->getBodyParam('type');
       		$like_object = Yii::$app->request->getBodyParam('like_object');
       		if($type == 1) {
       			if(!Post::findOne($like_object,['is_del' => 0])) {
       				$res['error'] = '动态已被删除';
       				return json_encode($res);
       			}
       		} elseif($type == 2) {
       			if(!Photo::findOne($like_object)) {
       				$res['error'] = '照片已被删除';
       				return json_encode($res);
       			}
       		} else {
       			$res['error'] = '非法操作';
       			return json_encode($res);
       		}
       		if($this->model->find()->where(['type' => $type,'like_object' => $like_object,'user_id' => Yii::$app->request->getBodyParam('id')])->one()) {
       			$res['flag'] = true;
       			return json_encode($res);
       		}
       		$this->model->user_id = Yii::$app->request->getBodyParam('id');
       		$this->model->addtime = time();
       		$this->model->save(false);
       		$res['flag'] = true;
       		return json_encode($res);
       } else {
       		$res['error'] = $this->model->errors;
       		return json_encode($res);
       }
    }

    /**
       * showdoc
       * @title 取消点赞
       * @description 取消点赞的接口
       * @catalog api文档/点赞相关
       * @method post
       * @url xxx/index.php?r=like/undo
       * @param  type 必选 int 点赞类型 1为动人，2为照片
       * @param  like_object 必选 int 点赞在数据id
       * @return {flag:true}
       * @return_param flag bool 操作结果表示。成功为true或1，失败为false或0
       * @return_param error array 错误信息
       * @remark 
       * @number 
       */
    public function actionUndo()
    {
    	$res['flag'] = false;
    	$this->model->attributes = Yii::$app->request->bodyParams;
    	if($this->model->validate()) {
    			$type = Yii::$app->request->getBodyParam('type');
    			$like_object = Yii::$app->request->getBodyParam('like_object');
    			if($type == 1) {
    				if(!Post::findOne($like_object,['is_del' => 0])) {
    					$res['error'] = '动态已被删除';
    					return json_encode($res);
    				}
    			} elseif($type == 2) {
    				if(!Photo::findOne($like_object)) {
    					$res['error'] = '照片已被删除';
    					return json_encode($res);
    				}
    			} else {
    				$res['error'] = '非法操作';
    				return json_encode($res);
    			}
    		    $this->model->deleteAll(['type' => $type,'like_object' => $like_object,'user_id' => Yii::$app->request->getBodyParam('id')]);
    			$res['flag'] = true;
    			return json_encode($res);
        } else {
        		$res['error'] = $this->model->errors;
        		return json_encode($res);
        }
    }

  

}
