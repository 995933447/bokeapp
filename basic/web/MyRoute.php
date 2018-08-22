<?php 
namespace My;

/**
 * 
 */
class MyRoute extends \yii\web\UrlRule
{
	public $pattern = "/.*?app.*?=.*?mod.*?=.*?act.*?=.*?/";
	// public function init()
	// {
	// 	var_dump($this->pattern);die;
	// }

	public function parseRuquest()
	{

	}

	public function createUrl($manager, $route, $params)
	{

	}
}


 ?>