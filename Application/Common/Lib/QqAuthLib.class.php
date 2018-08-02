<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/2
 * Time: 9:10
 */

namespace Common\Lib;


class QqAuthLib
{
	protected $appId;			//应用appId
	protected $appKey;			//应用唯一key
	protected $callBackUrl;		//授权结束回调地址

	public function __construct( $appId , $appKey, $callBackUrl )
	{
		$this->appId  = $appId;

		$this->appKey = $appKey;

		$this->callBackUrl = $callBackUrl;
	}

	/**
	 * 设置回调地址
	 * @param mixed $callBackUrl
	 */
	public function setCallBackUrl( $callBackUrl )
	{
		$this->callBackUrl = $callBackUrl;
	}


	/***
	 * 用户授权地址跳转
	 */
	public function auth()
	{
		$url = "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id={$this->appId}&redirect_uri=".urlencode ($this->callBackUrl)."&state=state&scope=get_user_info,list_album,upload_pic,do_like";
		redirect ( $url );
	}

	public function authLogin( $code )
	{
		//第二步，获取access_token
		$access_token = $this->getAccessToken ( $code );

		//第三步，获取openid
		$openid = $this->get_openid ( $access_token );

		//第四步，获取用户信息
		$userInfo = $this->get_user_info ( $access_token , $openid );

		$userInfo['uid'] = $openid;
		return $userInfo;
	}

	/***
	 * 获取用户授权信息
	 * @param $code
	 * @param string $msg
	 * @return bool|mixed
	 */
	public function getAccessToken( $code , & $msg = '' )
	{
		if ( !$code ) {
			$msg = '缺少参数';
			return false;
		}

		$url      = "https://graph.qq.com/oauth2.0/token";
		$param    = array(
			"grant_type"    => "authorization_code" ,
			"client_id"     => $this->appId ,
			"client_secret" => $this->appKey ,
			"code"          => $code ,
			"state"         => "state" ,
			"redirect_uri"  => $this->callBackUrl
		);
		$response = $this->get_url ( $url , $param );
		if ( !$response ) {
			return false;
		}
		$params = array();
		parse_str ( $response , $params );
		return $params[ "access_token" ];
	}

	/**
	 * 获取client_id 和 openid
	 * @param $access_token access_token验证码
	 * @return array 返回包含 openid的数组
	 * */
	private function get_openid( $access_token )
	{
		$url      = "https://graph.qq.com/oauth2.0/me";
		$param    = array(
			"access_token" => $access_token
		);
		$response = $this->get_url ( $url , $param );
		if ( $response == false ) {
			return false;
		}
		if ( strpos ( $response , "callback" ) !== false ) {
			$lpos     = strpos ( $response , "(" );
			$rpos     = strrpos ( $response , ")" );
			$response = substr ( $response , $lpos + 1 , $rpos - $lpos - 1 );
		}
		$user = json_decode ( $response );
		if ( isset( $user->error ) || $user->openid == "" ) {
			return false;
		}

		return $user->openid;
	}

	/**
	 * 获取用户信息
	 * @param  $access_token
	 * @param $openid
	 * @return array 用户的信息数组
	 * */
	public function get_user_info( $access_token , $openid )
	{
		$url = 'https://graph.qq.com/user/get_user_info?oauth_consumer_key=' . $this->appId . '&access_token=' . $access_token . '&openid=' . $openid . '&format=json';
		$str = $this->get_url ( $url );
		if ( $str == false ) {
			return false;
		}
		$arr = json_decode ( $str , true );
		return $arr;
	}

	/*
     * HTTP POST Request请求获取数据
    */
	public function post_url( $url , $params )
	{
		$ch = curl_init ();
		if ( stripos ( $url , "https://" ) !== false ) {
			curl_setopt ( $ch , CURLOPT_SSL_VERIFYPEER , false );
			curl_setopt ( $ch , CURLOPT_SSL_VERIFYHOST , false );
		}

		curl_setopt ( $ch , CURLOPT_URL , $url );
		curl_setopt ( $ch , CURLOPT_RETURNTRANSFER , 1 );
		curl_setopt ( $ch , CURLOPT_POST , true );
		curl_setopt ( $ch , CURLOPT_POSTFIELDS , $params );
		$content = curl_exec ( $ch );
		$status  = curl_getinfo ( $ch );
		curl_close ( $ch );
		if ( intval ( $status[ "http_code" ] ) == 200 ) {
			return $content;
		} else {
			return false;
		}
	}

	/***
	 * HTTP GET 方式获取数据
	 * @param $url
	 * @return bool|mixed
	 */
	public function get_url( $url , $param = null )
	{
		if ( $param != null ) {
			$query = http_build_query ( $param );
			$url   = $url . '?' . $query;
		}
		$ch = curl_init ();
		if ( stripos ( $url , "https://" ) !== false ) {
			curl_setopt ( $ch , CURLOPT_SSL_VERIFYPEER , false );
			curl_setopt ( $ch , CURLOPT_SSL_VERIFYHOST , false );
		}

		curl_setopt ( $ch , CURLOPT_URL , $url );
		curl_setopt ( $ch , CURLOPT_RETURNTRANSFER , 1 );
		$content = curl_exec ( $ch );
		$status  = curl_getinfo ( $ch );
		curl_close ( $ch );
		if ( intval ( $status[ "http_code" ] ) == 200 ) {
			return $content;
		} else {
			echo $status[ "http_code" ];
			return false;
		}
	}
}