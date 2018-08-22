<?php
namespace app\controllers;
use app\controllers\CommonController;
use app\models\User;
use app\models\Post;
use app\models\Photo;
use app\models\Collect;
use Yii;

class CollectController extends CommonController
{
	private $model;

    private $count = 15;

	public function init()
	{
		parent::init();
		$this->model = new Collect;
	}

    /**
       * showdoc
       * @title 收藏
       * @description 收藏的接口
       * @catalog api文档/收藏相关
       * @method post
       * @url xxx/index.php?r=collect
       * @param  type 必选 int 收藏类型 1为动态，2为照片
       * @param  collect_object 必选 int 收藏的数据id,post_id或photo_id
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
        	$collect_object = Yii::$app->request->getBodyParam('collect_object');
        	if($type == 1) {
        		if(!Post::findOne($collect_object,['is_del' => 0])) {
        			$res['error'] = '动态已被删除';
        			return json_encode($res);
        		}
        	} elseif($type == 2) {
        		if(!Photo::findOne($collect_object)) {
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

    /**
       * showdoc
       * @title 取消收藏
       * @description 取消收藏的接口
       * @catalog api文档/收藏相关
       * @method post
       * @url xxx/index.php?r=collect/undo
       * @param  type 必选 int 收藏类型 1为动态，2为照片
       * @param  collect_object 必选 int 收藏的数据id
       * @return {flag:true}
       * @return_param flag bool 操作结果表示。成功为true或1，失败为false或0
       * @return_param error array 错误信息
       * @remark 
       * @number 
       */
    public function actionUndo()
    {
       $res['flag'] = false;
       $data = Yii::$app->request->bodyParams;
       $this->model->attributes = $data;
       if($this->model->validate()) {
            $type = Yii::$app->request->getBodyParam('type');
            $collect_object = Yii::$app->request->getBodyParam('collect_object');
            $this->model->find()->where(['type' => $type,'collect_object' => $collect_object,'user_id' => Yii::$app->request->getBodyParam('id')])->delete();
            $res['flag'] = true;
            return json_encode($res);
       } else {
            $res['error'] = $this->model->errors;
            return json_encode($res);
       }
    }

    /**
       * showdoc
       * @title 收藏列表
       * @description 收藏列表的接口
       * @catalog api文档/收藏相关
       * @method post
       * @url xxx/index.php?r=collect/get-data
       * @param  type 必选 int 收藏类型 1为动态，2为照片
       * @param  last_id 必选 int 上次下拉获取最后一条收藏数据的id
       * @param  count 必选 int 一次下拉获取的条数
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
                $data = $this->model->find()->with(['post.photo','post.vedio','post.user.userinfo'])->where(['type' => $type,'user_id' => Yii::$app->request->getBodyParam('id')])->andWhere(['<','id',$last_id])->limit($count)->orderBy('id DESC')->asArray()->all();
            } else {
                $data = $this->model->find()->with(['post.photo','post.vedio','post.user.userinfo'])->where(['type' => $type,'user_id' => Yii::$app->request->getBodyParam('id')])->limit($count)->orderBy('id DESC')->asArray()->all();
            }    
        } elseif(intval($type) == 2) {
            if($last_id) {
                $data = $this->model->find()->with(['photo.user.userinfo'])->where(['type' => $type,'user_id' => Yii::$app->request->getBodyParam('id')])->andWhere(['<','id',$last_id])->limit($count)->orderBy('id DESC')->asArray()->all();
            } else {
                $data = $this->model->find()->with(['photo.user.userinfo'])->where(['type' => $type,'user_id' => Yii::$app->request->getBodyParam('id')])->limit($count)->orderBy('id DESC')->asArray()->all();
            }    
        }
        $data_output = [];
        if($data) {
            foreach ($data as $key => $value) {
                $data_output[$key]['type'] = $value['type'];
                $data_output[$key]['collect_object'] = $value['collect_object'];
                $data_output[$key]['collect_id'] = $value['id'];
                $data_output[$key]['add_date'] = date('Y-m-d H:i:s',$value['addtime']);
                $data_output[$key]['addtime'] = $value['addtime'];
                if((isset($value['post']) && $value['post']) || (isset($value['photo']) && $value['photo'])) {
                    if((isset($value['post']) && $value['post'])) {
                        $data_output[$key]['post'] = [
                            'content' => $value['post']['content'],
                            'add_date' => date('Y-m-d H:i:s',$value['post']['addtime']),
                            'addtime' => $value['post']['addtime'],
                            'user_id' => $value['post']['user_id'],
                            'username' => $value['post']['user']['username'],
                            'post_id' => $value['post']['id'],
                        ];
                        if($value['post']['user']['userinfo'] && $value['post']['user']['userinfo']['face']) {
                           $data_output[$key]['post']['face'] = $this->addFileBase($value['post']['user']['userinfo']['face']);
                        }
                        if($value['post']['photo']) {
                            foreach ($value['post']['photo'] as $k => $v) {
                                $data_output[$key]['post']['photo'][] = $this->addFileBase($v['photo']);
                            }
                        }
                        if($value['post']['vedio']) {
                            $data_output[$key]['post']['vedio'] = $this->addFileBase($value['vedio']['vedio']);
                        }     
                    }
                    if((isset($value['photo']) && $value['photo'])) {
                        $data_output[$key]['photo']['photo'] = $this->addFileBase($value['photo']['photo']);
                        $data_output[$key]['photo']['photo_id'] = $value['photo']['id'];
                        $data_output[$key]['photo']['addtime'] = $value['photo']['addtime'];
                        $data_output[$key]['photo']['add_date'] = date('Y-m-d H:i:s',$value['photo']['addtime']);
                        $data_output[$key]['photo']['username'] = $value['photo']['user']['username'];
                        $data_output[$key]['photo']['user_id'] = $value['photo']['user']['id'];
                        if($value['photo']['user']['userinfo'] && $value['photo']['user']['userinfo']['face']) $data_output[$key]['photo']['face'] = $value['photo']['user']['userinfo']['face'];
                    }
                } else {
                    if(intval($type) == 1) {
                        $data_output[$key]['post'] = '该动态已被永久删除';
                    } else {
                        $data_output[$key]['photo'] = '该照片已被永久删除';
                    }
                }
            }
            $res['data'] = $data_output;
            return json_encode($res);
        }

    }

}
