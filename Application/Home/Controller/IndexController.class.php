<?php

namespace Home\Controller;

use Common\Lib\QqAuthLib;
use Common\Lib\WeiBoAuthLib;
use Common\Logic\UserLogic;
use Think\Controller;

class IndexController extends Controller
{
	/**
	 * 登陆页面
	 */
	public function login()
	{
		return $this->display ();
	}

	/**
	 * 微博登陆
	 */
	public function weibo_login()
	{
		if ( session ( "userData" ) ) {
			var_dump ( "用户已登陆" );
			exit;
		}

		$WeiboLib = new WeiBoAuthLib( C ( 'WEIBO_AUTH.APP_ID' ) , C ( 'WEIBO_AUTH.APP_KEY') , C ( "WEIBO_AUTH.CALLBACK" ) );
		$WeiboLib->auth ();
	}

	/**
	 * qq登陆
	 */
	public function qq_login()
	{
		if ( session ( "userData" ) ) {
			var_dump ( "用户已登陆" );
			exit;
		}

		$QqAuthLib = new QqAuthLib( C ( 'QQ_AUTH.APP_ID' ) , C ( 'QQ_AUTH.APP_KEY' ), C ( "QQ_AUTH.CALLBACK" ) );
		$QqAuthLib->auth ();
	}

	/**
	 * 微博回调地址
	 */
	public function index()
	{
		$code = I ( "get.code" , '' );
		if ( !$code ) {
			echo '参数错误';
			exit;
		}

		//获取用户的基本信息
		$WeiboLib = new WeiBoAuthLib( C ( 'WEIBO_AUTH.APP_ID' ) , C ( 'WEIBO_AUTH.APP_KEY' ), C ( "WEIBO_AUTH.CALLBACK" ) );
		$data = $WeiboLib->authLogin ( $code );
		if ( !$data ) {
			echo '授权获取用户的基本信息出错';
			exit;
		}

		//登陆设置session
		$UserLogic = new UserLogic();
		$re        = $UserLogic->userAuthLogin ( $data[ 'uid' ] , $data , 1 );
		if ( !$re ) {
			echo '用户注册登陆失败';
			exit;
		}

		//跳转网站首页
		redirect ( U ( 'Index/home' ) );
	}

	/**
	 * 退出登陆
	 */
	public function logout()
	{
		session ( "userData" , null );
		redirect ( U ( 'Index/login' ) );
	}

	/***
	 * QQ回调地址
	 */
	public function qq_callback()
	{
		$code = I ( "get.code" , '' );

		if ( !$code ) {
			echo '参数错误';
			exit;
		}

		//获取access_token
		$QqAuthLib = new QqAuthLib( C ( 'QQ_AUTH.APP_ID' ) , C ( 'QQ_AUTH.APP_KEY' ), C ( "QQ_AUTH.CALLBACK" ) );
		$data = $QqAuthLib->authLogin ( $code );
		if ( !$data ) {
			echo '授权出错';
			exit;
		}

		//登陆设置session
		$UserLogic = new UserLogic();
		$re        = $UserLogic->userAuthLogin ( $data[ 'uid' ] , $data , 2 );
		if ( !$re ) {
			echo '用户登陆失败';
			exit;
		}

		//跳转网站首页
		redirect ( U ( 'Index/home' ) );
	}

	/**
	 * 网站首页
	 */
	public function home()
	{
		$user = session("userData");

		$this->assign('data', $user);

		return $this->display();
	}
}