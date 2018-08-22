<?php 
namespace app\controllers;
use app\models\User;
use yii\base\DynamicModel;
use yii\helpers\Url;

class CommonController extends \yii\web\Controller
{
	protected $attributes_errors;

	protected $time_range = 15 * 60;

	/**
	 * [init token公共验证]
	 * @return [type] [description]
	 */
	public function init()
	{
		$this->enableCsrfValidation = false;
	    // $this->checkSignuture();
	}

	/**
	 * [checkSignuture 检测合法访问签名]
	 * @return [type] [description]
	 */
	/**
	    * showdoc
	    * @title 用户签名
	    * @description 公告参数
	    * @catalog api文档/公告部分(必读)
	    * @method post
	    * @param token 必选 string 用户密钥  
	    * @param id 必选 int 用户id
	    * @param app_id 必选 string app id
	    * @param time 必选 int 客户端请求时js生成时间戳,请求时间与服务器接收时间如果超过15分钟就判定请求违法  
	    * @return {flag:false,error:'缺少用户id'}
	    * @return_param flag bool 操作结果表示。成功为true或1，失败为false或0
	    * @return_param error string 错误信息
	    * @remark 除了用户注册登录部分接口外以上为全局接口请求参数，即每次请求都要带上以上参数，请将以上请求参数封装放在请求体内，否则可能会被接口拒绝访问。注册登录部分接口无需携带以上参数，以上参数用来校验用户是否登录以及身份合法性
	    * @number 
	    */
	protected function checkSignuture()
	{
		$this->attributes_errors = [
			'token' => '缺少参数oken',
			'id' => '缺少用户id',
			'app_id' => '缺少参数app id',
			'time' => '缺少参数time',
		];
		$this->checkAtrributes();
		$res['flag'] = false;
		$token = \Yii::$app->request->getBodyParam('token');
		$id = \Yii::$app->request->getBodyParam('id');
		$app_id = \Yii::$app->request->getBodyParam('app_id');
		$check_token = \Yii::$app->phpredis->get('user_'.$id.'_'.$app_id.'_token');
		$check_token = json_decode($check_token,true);
		$time = \Yii::$app->request->getBodyParam('time');
		if(!$check_token) {
			$res['error'] = '用户未登陆';
			echo json_encode($res);
			die;
		}
		if($token != $check_token['token']) {
			$res['error'] = 'token不合法';
			echo json_encode($res);
			die;
		} elseif(intval($check_token['expire']) < time()) {
			$res['error'] = 'token已过期，请重新登陆';
			echo json_encode($res);
			die;
		}
		//根据请求发出时间判断是否请求拦截
		if(isset($this->attributes_errors['time'])) {
			//判断客户端时间与服务器时间是否单位一致并转化一致
			$time_len = strlen($time);
			$time_more_len = $time_len - 10;
			if($time_more_len) {
				$tran_time_num = 1;
				for ($i=0; $i <$time_more_len ; $i++) { 
					$tran_time_num = $tran_time_num * 10;
				}
				$time = $time / $tran_time_num;
				$time = intval($time);
			}
			// 判断请求是否被拦截嫌疑
			if(($time - time()) < -1 * $this->time_range || ($time - time()) > $this->time_range) {
				\Yii::$app->phpredis->delete('user_'.$id.'_'.$app_id.'_token');
				$res['error'] = '请求时间与实际时间差距过大，请求可能已被拦截，请重新登陆';
				echo json_encode($res);
				die;
			}
		}
	}

	/**
	 * [checkAtrributes 检查签名参数是否齐全]
	 * @return [type] [description]
	 */
	protected function checkAtrributes()
	{
		$res['flag'] = false;
		foreach ($this->attributes_errors as $key => $value) {
			if(!\Yii::$app->request->getBodyParam($key)) {
				$res['error'] = $value;
				echo json_encode($res);
				die;
			}
		}
	}

	/**
	 * [addFileBase 为文件路径添加项目网站根目录]
	 * @param [type] $file [description]
	 */
	protected function addFileBase($file)
	{	
		return \Yii::$app->filedeal->addFileBase($file);
	}

	/**
	 * [formatFILES 将$_FILES格式化为yii\web\UploadedFile支持格式]
	 * @param  [type] $model_name [模型名称 首字母大写]
	 * @param  [type] $file_name  [表单项名称]
	 * @return [type]             [description]
	 */
	protected function formatFILES($model_name,$file_name)
	{
		\Yii::$app->filedeal->formatFILES($model_name,$file_name);
	}

}

 ?>