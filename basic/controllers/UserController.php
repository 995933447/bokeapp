<?php
namespace app\controllers;
use app\models\User;
use yii\base\DynamicModel;

/**
 * 用户控制器
 */
class UserController extends \yii\web\Controller
{   

	public $model; //用户模型

    /**
     * [init 初始化方法]
     * @return [type] [description]
     */
	public function init()
	{
		parent::init();
        $this->enableCsrfValidation = false;
		$this->model = new User();
	}


    /**
        * showdoc
        * @title 用户注册
        * @description 用户注册的接口
        * @catalog api文档/注册登录
        * @method post
        * @url xxx/index.php?r=user/register
        * @param username 必选 string 用户名  
        * @param password 必选 string 密码
        * @param password_repeat 必选 string 确认密码
        * @param phone 必选 string 手机号码  
        * @param code 必选 string 验证码
        * @return {flag:true}
        * @return_param flag bool 操作结果表示。成功为true或1，失败为false或0
        * @return_param error array 错误信息
        * @remark 
        * @number 
        */
    public function actionRegister()
    {
    	$res['flag'] = false;
        $data = \Yii::$app->request->bodyParams;
        $this->model->setScenario('app_register');
        $this->model->attributes = $data;
     	if($this->model->validate()) {
            $phpredis = \Yii::$app->phpredis;
            $code = $redis->get($this->model->phone.'_valicodes');
            $code = json_decode($code,true);
            if(!$code) {
                $res['error'] = '请输入手机验证码';
            } elseif((intval($code['expire']) + intval($code['created_at'])) < time()) {
                $res['error'] = '手机验证码已过期';
            } elseif(intval($code['code']) !== intval($this->model->code)) {
                $res['error'] = '手机验证码错误';
            }
            if(isset($res['error']) && $res['error']) return json_encode($res);
            if($this->model->find()->select('id')->where(['username' => $this->model->username,'app_id' => $this->model->app_id])->one()) {
                $res['error'] = '用户名已存在';
                return json_encode($res);
            }
            if($this->model->find()->select('id')->where(['phone' => $this->model->phone,'app_id' => $this->model->app_id])->one()) {
                $res['error'] = '该手机已注册';
                return json_encode($res);
            }
            $this->model->password = md5($this->model->password);
            $this->model->register_time = time();
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
        * @title 获取手机验证码
        * @description 获取手机验证码的接口
        * @catalog api文档/注册登录
        * @method post
        * @url xxx/index.php?r=user/code
        * @param phone 必选 string 手机号码  
        * @return {flag:true}
        * @return_param flag bool 操作结果表示。成功为true或1，失败为false或0
        * @return_param error array 错误信息
        * @remark 
        * @number 
        */
    public function actionCode()
    {  
    	$res['flag'] = false;
    	$phone = \Yii::$app->request->getBodyParam('phone');
        $phone = trim($phone);
    	$model = DynamicModel::validateData(compact('phone'),[
    		['phone','trim'],
    		['phone','required','message' => '请输入注册手机号码'],
    		['phone','match','pattern' => '/^1[34578]\d{9}$/','message' => '手机格式不正确'],
    	]);
    	if($model->hasErrors()) {
    		$res['error'] = $model->errors;
    		return json_encode($res);
    	} else {
            if($check_requested = $redis->get($phone.'_valicodes')){
                $check_requested = json_decode($check_requested);
                if((time() - $check_requested['created_at']) < 60) {
                    $res['error'] = '1分钟内1个号码最多能请求一个验证码';
                    return json_encode($res);
                }
            };
    		$code = '';
    		for ($i=0; $i < 4 ; $i++) { 
    			$code .= rand(0,9); 
    		}
            $rep = $this->actionSendMess($phone,$code);
            if(!$rep['falg']) {
                return json_encode($rep);
            }
    	}
        $redis = \Yii::$app->phpredis;
        $redis->set($phone.'_valicodes',json_encode(['code' => $code,'expire' => 60*5,'created_at' => time()]));
        $res['flag'] = false;
        $res['code'] = $code;
        return json_encode($res);
    }

    protected function actionSendMess($phone,$mess)
    {    
         $res['flag'] = false;
         $rep = file_get_contents("http://utf8.api.smschinese.cn/?Uid=bobby7&Key=d41d8cd98f00b204e980&smsMob={$phone}&smsText={$mess}");
         if(intval($rep) !== 1) {
                switch ($rep) {
                    case '-1':
                            $res['error'] = '没有该用户账户';
                        break;
                    case '-2':
                            $res['error'] = '非法账户';
                        break;
                    case '-21':
                            $res['error'] = '非法账户';
                        break;
                    case '-3':
                            $res['error'] = '短信数量不足';
                        break;
                    case '-11':
                            $res['error'] = '服务器账户被禁用';
                        break;
                    case '-14':
                            $res['error'] = '服务器账户被禁用';
                        break;
                    case '-41':
                            $res['error'] = '手机号码为空';
                        break;
                    case '-42':
                            $res['error'] = '短信内容为空';
                        break;
                    case '-51':
                            $res['error'] = '服务器账户出错';
                        break;
                    case '-52':
                            $res['error'] = '服务器账户出错';
                        break;
                    case '-6':
                            $res['error'] = '短信发送数量';
                        break;
                    default:
                            $res['error'] = '未知错误';
                        break;
                }
         }
         if(!isset($res['error'])) {
             $res['flag'] = true;
         }
         return $res;
    }

    /**
        * showdoc
        * @title 用户登录
        * @description 用户登录的接口
        * @catalog api文档/注册登录
        * @method post
        * @url xxx/index.php?r=user/login
        * @param username 可选 string 用户名  
        * @param password 必选 string 密码
        * @param phone 可选 string 手机号码  
        * @return {flag:true,token:'123'}
        * @return_param flag bool 操作结果表示。成功为true或1，失败为false或0
        * @return_param error array 错误信息
        * @return_param token string 用户秘钥 
        * @remark 
        * @number 
        */
    public function actionLogin()
    {
        $res['flag'] = false;
        $params =\Yii::$app->request->bodyParams;
        $this->model->setScenario('app_login');
        $this->model->attributes = $params;
        if(!$this->model->validate()) {
            $res['error'] = $this->model->errors;
            return json_encode($res);
        }
        if($this->model->username) {
            $user_info = $this->model->find()->select(['password','id','app_id'])->where(['username' => $this->model->username,'app_id' => $this->model->app_id])->one();
        } else {
            $user_info = $this->model->find()->select(['password','id','app_id'])->where(['phone' => $this->model->phone,'app_id' => $this->model->app_id])->one();
        }
        if(!$user_info) {
            $res['error'] = '用户不存在';
            return json_encode($res);
        }
        $password = $user_info['password'];
        if(md5($this->model->password) != $password) {
            $res['error'] = '密码不正确';
            return json_encode($res);
        }
        $token = md5(uniqid(microtime(true),true));
        $expire = time() + 3600 * 24 * 7;
        $this->actionCreateToken($user_info,$token,$expire);
        $res['flag'] = true;
        $res['token'] = $token;
        $res['id'] = $user_info->id;
        return json_encode($res);
    }

    /**
     * [actionCreateToken 创建登陆token]
     * @param  [type] $token  [description]
     * @param  [type] $expire [description]
     * @return [type]         [description]
     */
    private function actionCreateToken($model,$token,$expire)
    {
        // $model->setScenario('app_login');
        // $model->token = $token;
        // $model->expire = $expire;
        // $model->save(false);
        $redis = \Yii::$app->phpredis;
        $redis->set('user_'.$model->id.'_'.$model->app_id.'_token',json_encode(['token' => $token,'expire' => $expire]));
    }

    /**
        * showdoc
        * @title 用户登录
        * @description 用户登录的接口
        * @catalog api文档/注册登录
        * @method post
        * @url xxx/index.php?r=user/login-out
        * @param id 可选 int 用户id  
        * @param token 必选 string 秘钥
        * @param app_id 可选 string app id  
        * @return {flag:true}
        * @return_param flag bool 操作结果表示。成功为true或1，失败为false或0
        * @return_param error array 错误信息
        * @remark 
        * @number 
        */
    public function actionLoginOut()
    {
        $res['flag'] = false;
        $id = \Yii::$app->request->getBodyParam('id');
        $token = \Yii::$app->request->getBodyParam('token');
        $app_id = \Yii::$app->request->getBodyParam('app_id');
        if(!$id) {
            $res['error'] = '缺少用户id';
            return json_encode($res);
        } 
        if(!$token) {
            $res['error'] = '缺少token';
            return json_encode($res);
        }
        if(!$app_id) {
            $res['error'] = '缺少app id';
            return json_encode($res);
        }
        $redis = \Yii::$app->phpredis;
        $token_data = $redis->get('user_'.$id.'_'.$app_id.'_token');
        $token_data = json_decode($token_data,true);
        if(!$token_data) {
            $res['flag'] = true;
            $res['message'] = '用户未曾登陆,请清除此设备token';
        } elseif($token != $token_data['token']) {
            $res['flag'] = true;
            $res['message'] = '该用户已在其他设备登陆,请清除此设备token';
        } else {
            $redis->delete('user_'.$id.'_'.$app_id.'_token');
            $res['flag'] = true;
        }
        return json_encode($res);
    }

    /**
        * showdoc
        * @title 找回密码
        * @description 找回密码的接口
        * @catalog api文档/注册登录
        * @method post
        * @url xxx/index.php?r=user/reset
        * @param phone 必选 string 手机号码  
        * @param app_id 必选 string app id
        * @return {flag:true}
        * @return_param flag bool 操作结果表示。成功为true或1，失败为false或0
        * @return_param error array 错误信息
        * @remark 
        * @number 
        */
    public function actionReset()
    {
        $res['flag'] = false;
        $app_id = \Yii::$app->request->getBodyParam('app_id');
        $phone = \Yii::$app->request->getBodyParam('phone');
        $phone = trim($phone);
        $model = DynamicModel::validateData(compact('phone','app_id'),[
            ['app_id','required','message' => '缺少app id'],
            ['phone','trim'],
            ['phone','required','message' => '请输入注册手机号码'],
            ['phone','match','pattern' => '/^1[34578]\d{9}$/','message' => '手机格式不正确'],
        ]);
        if($model->hasErrors()) {
            $res['error'] = $model->errors;
            return json_encode($res);
        } else {
            $check = $this->model->find()->select(['id','password'])->where(['app_id' => $app_id,'phone' => $phone])->one();
            if(!$check) {
                $res['error'] = '用户不存在';
                return json_decode($res);
            }
            $code = '';
            for ($i=0; $i < 6; $i++) { 
                $code .= rand(0,9);
            }
            $mess = '您的密码重置成功，请记住您的新密码并重新登陆修改密码。新密码：{code}。';
            $rep = $this->actionSendMess($phone,$mess);
            if(!$rep['flag']) {
                return json_encode($rep);
            }
            $check->password = md5($code);
            $check->save(false);
            $res['flag'] = true;
            return $res;
        }
    }

}
