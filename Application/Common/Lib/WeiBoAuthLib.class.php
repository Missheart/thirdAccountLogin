<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/2
 * Time: 9:10
 */

namespace Common\Lib;


class WeiBoAuthLib
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

	/***
	 * 用户授权地址跳转
	 */
	public function auth()
	{
		$url = "https://api.weibo.com/oauth2/authorize?client_id=" . $this->appId . "&response_type=code&redirect_uri=" . $this->callBackUrl;
		redirect ( $url );
	}

	public function authLogin($code, &$msg = ''){
		if( !$code ){
			$msg = '参数缺失';
			return false;
		}

		$data = $this->getAccessTokenData ( $code );
		if ( !$data ) {
			$msg = '授权出错';
			return false;
		}
		$access_token = $data[ 'access_token' ];
		$uid          = $data[ 'uid' ];

		//获取到用户数据
		$userData = $this->getUserInfo ( $uid , $access_token );
		if ( !$userData ) {
			$msg = '获取用户数据出错';
			return false;
		}

		$userData['uid'] = $uid;
		return $userData;
	}

	/***
	 * 获取用户授权信息
	 * @param $code
	 * @param string $msg
	 * @return bool|mixed
	 */
	public function getAccessTokenData( $code , &$msg = '' )
	{
		if ( !$code ) {
			$msg = '缺少参数';
			return false;
		}
		$url  = "https://api.weibo.com/oauth2/access_token?client_id=" . $this->appId . "&client_secret=" . $this->appKey . "&grant_type=authorization_code&redirect_uri=" . $this->callBackUrl . "&code=" . $code;
		$data = $this->httpPost ( $url , array() );
		if ( !is_array ( $data ) || !$data[ 'access_token' ] ) {
			$msg = '授权出错1';
			return false;
		}

		return $data;
	}

	/**
	 * 获取用户详细信息
	 * @param $uid
	 * @param $accessToken
	 * @param string $msg
	 * @return bool|mixed
	 */
	public function getUserInfo( $uid , $accessToken, &$msg = '' )
	{
		$url = "https://api.weibo.com/2/users/show.json?access_token={$accessToken}&uid={$uid}";
		$userData = $this->httpGet($url);
		if( !$userData ){
			$msg = '获取用户信息失败';
			return false;
		}

		return $userData;
	}

	/***
	 * http post请求数据
	 * @param $url
	 * @param $post_data
	 * @return bool|mixed
	 */
	private function httpPost( $url , $post_data )
	{
		$curl = curl_init ();
		curl_setopt ( $curl , CURLOPT_URL , $url );
		curl_setopt ( $curl , CURLOPT_HEADER , 1 );
		curl_setopt ( $curl , CURLOPT_RETURNTRANSFER , 1 );
		curl_setopt ( $curl , CURLOPT_POST , 1 );
		curl_setopt ( $curl , CURLOPT_POSTFIELDS , $post_data );
		//执行命令
		$data = curl_exec ( $curl );
		if ( curl_errno ( $curl ) ) {
			$msg = curl_error ( $curl );;
			return false;
		}
		//关闭URL请求
		curl_close ( $curl );
		//显示获得的数据
		$data = substr ( $data , strpos ( $data , "{" ) );

		return json_decode ( $data , true );
	}

	/***
	 *
	 * http get get方式获取数据
	 * @param $url
	 * @return bool|mixed
	 */
	private function httpGet( $url )
	{
		$curl = curl_init ();
		curl_setopt ( $curl , CURLOPT_URL , $url );
		curl_setopt ( $curl , CURLOPT_HEADER , 1 );
		curl_setopt ( $curl , CURLOPT_RETURNTRANSFER , 1 );
		$data = curl_exec ( $curl );
		curl_close ( $curl );
		if ( curl_errno ( $curl ) ) {
			$msg = curl_error ( $curl );;
			return false;
		}

		$data = substr ( $data , strpos ( $data , "{" ) );
		return json_decode ( $data , true );
	}

}