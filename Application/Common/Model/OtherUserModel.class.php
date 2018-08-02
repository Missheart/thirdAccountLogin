<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/2
 * Time: 9:41
 */

namespace Common\Model;

class OtherUserModel extends BaseModel
{
	protected $getField = 'id, nickname, img, user_id, uid, type, created_at, updated_at';
}