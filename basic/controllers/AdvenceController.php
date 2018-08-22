<?php

namespace app\controllers;
use app\models\Advence;
use app\controllers\CommonController;

class AdvenceController extends CommonController
{
	private $model;
	
	public function init()
	{
		parent::init();
		$this->model = new Advence();
	}

  /**
     * showdoc
     * @title 意见反馈
     * @description 意见反馈的接口
     * @catalog api文档/意见反馈相关
     * @method post
     * @url xxx/index.php?r=advence
     * @param  content 必选 string 反馈内容
     * @return {flag:true}
     * @return_param flag bool 操作结果表示。成功为true或1，失败为false或0
     * @return_param error array 错误信息
     * @remark 
     * @number 
     */
  public function actionIndex()
  {
    $data = \Yii::$app->request->bodyParams;
    $this->model->attributes = $data;
    if($this->model->validate()) {
       $this->model->addtime = time();
       $this->model->user_id = \Yii::$app->request->getBodyParam('id');
       $this->model->save(false);
       $res['flag'] = true;
       return json_encode($res);
    }
  }

}
