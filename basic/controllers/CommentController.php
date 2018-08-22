<?php

namespace app\controllers;
use app\controllers\CommonController;
use app\models\User;
use app\models\Post;
use app\models\Photo;
use app\models\Comment;
use Yii;

class CommentController extends CommonController
{
	private $model;

	public function init()
	{
		parent::init();
		$this->model = new Comment;
	}

    /**
       * showdoc
       * @title 评论
       * @description 评论的接口
       * @catalog api文档/评论相关
       * @method post
       * @url xxx/index.php?r=comment
       * @param  type 必选 int 点赞类型 1为动态，2为照片，post_id或photo_id
       * @param  content 必选 string 评论的内容
       * @param  comment_object 必选 int 评论的数据id
       * @return {flag:true}
       * @return_param flag bool 操作结果表示。成功为true或1，失败为false或0
       * @return_param error array 错误信息
       * @remark 
       * @number 
       */
    public function actionIndex()
    {
    	$res['flag'] = false;
        $data = Yii::$app->request->bodyParams;
        $this->model->attributes = $data;
        if($this->model->validate()) {
        	$type = Yii::$app->request->getBodyParam('type');
        	$comment_object = Yii::$app->request->getBodyParam('comment_object');
        	if($type == 1) {
        		if(!Post::findOne($comment_object,['is_del' => 0])) {
        			$res['error'] = '动态已被删除';
        			return json_encode($res);
        		}
        	} elseif($type == 2) {
        		if(!Photo::findOne($comment_object)) {
        			$res['error'] = '照片已被删除';
        			return json_encode($res);
        		}
        	} else {
        		$res['error'] = '非法操作';
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

}
