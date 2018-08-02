<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/2
 * Time: 10:08
 */

namespace Common\Logic;


use Common\Model\OtherUserModel;
use Common\Model\UserModel;

class UserLogic
{
	/***
	 * 用户授权登陆
	 * @param $uid
	 * @param $userData
	 * @param int $type
	 * @param string $msg
	 * @return bool
	 */
	public function userAuthLogin( $uid , $userData, $type = 1, &$msg = '')
	{
		$OtherUser  = new OtherUserModel();
		$User       = new UserModel();
		$other_user = $OtherUser->getOne (['uid'=>$uid]);
		if ( !$other_user ) {
			//注册
			$img = '';
			$nickname = '';
			if( $type == 1 ){
				//微博
				$img = $userData[ 'profile_image_url' ];
				$nickname = $userData[ 'screen_name' ];
			}else if( $type ==  2 ){
				//QQ
				$img = $userData[ 'figureurl' ];
				$nickname = $userData[ 'nickname' ];
			}

			try {
				//注册
				$OtherUser->startTrans ();
				$id = $User->addOne ( array( 'created_at' => NOW_TIME , 'updated_at' => NOW_TIME , 'img' => $img , 'nickname' =>$nickname  ) );
				if ( !$id ) {
					$msg = '登陆出错3';
					throw_exception ($msg);
				}
				$re = $OtherUser->addOne ( array( 'user_id' => $id , 'uid' => $uid , 'type' => $type , 'created_at' => NOW_TIME , 'updated_at' => NOW_TIME , 'img' => $img , 'nickname' => $nickname, 'text' => json_encode ( $userData ) ) );
				if ( !$re ) {
					$msg = '登陆出错4';
					throw_exception ($msg);
				}
				$OtherUser->commit ();
			}
			catch ( \Exception $e ) {
				$OtherUser->rollback ();
				return false;
			}

			$other_user = $OtherUser->getOne (['uid'=>$uid]);
		} else {
			//已注册，修改数据
			$OtherUser->saveOne (['uid' => $uid], [ 'text' => json_encode ( $userData ) , 'updated_at' => time () ] );
		}

		$user = $User->getOne (['id' => $other_user[ 'user_id' ]]);
		if ( !$user ) {
			$msg =  '用户数据有误5';
			return false;
		}
		$user[ 'uid' ] = $uid;
		session ( 'userData' , $user );

		return true;
	}
}