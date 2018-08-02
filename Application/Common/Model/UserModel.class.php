<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/2
 * Time: 9:41
 */

namespace Common\Model;

class UserModel extends BaseModel
{
	protected $getField = 'id, nickname, img, account, password, created_at, updated_at';
}