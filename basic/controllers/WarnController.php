<?php
namespace app\controllers;
use app\controllers\CommonController;
use app\models\User;
use app\models\Post;
use app\models\Photo;
use app\models\Warn;
use Yii;

class WarnController extends CommonController
{
	private $model;

	public function init()
	{
		parent::init();
		$this->model = new Warn;
	}

    /**
       * showdoc
       * @title 举报
       * @description 举报的接口
       * @catalog api文档/举报相关
       * @method post
       * @url xxx/index.php?r=warn
       * @param  type 必选 int 举报类型 1动态，2照片
       * @param  warn_object 必选 int 要举报的数据id，post_id或photo_id
       * @param  content 可选 string 举报说明
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
        	$warn_object = Yii::$app->request->getBodyParam('warn_object');
        	if($type == 1) {
        		if(!Post::findOne($warn_object,['is_del' => 0])) {
        			$res['error'] = '动态已被删除';
        			return json_encode($res);
        		}
        	} elseif($type == 2) {
        		if(!Photo::findOne($warn_object)) {
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
