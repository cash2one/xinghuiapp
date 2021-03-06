<?php
namespace Admin\Controller;
use Think\Controller;
class VerifyController extends Controller {
    public function index(){
		$w = 105;
		$h = 40;
		$config =    array(
			'imageW'	  =>	$w,
			'imageH'	  =>	$h,
			'fontSize'    =>    15,    // 验证码字体大小
			'length'      =>    4,     // 验证码位数
			'useNoise'    =>    false, // 关闭验证码杂点
		);
		$Verify = new \Think\Verify($config);
		$Verify->entry();
    }
}