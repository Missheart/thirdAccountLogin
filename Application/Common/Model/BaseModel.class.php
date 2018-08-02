<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/2
 * Time: 9:41
 */

namespace Common\Model;

use Think\Model;

class BaseModel extends Model
{
	protected $getField = '*';

	/**
	 * @param mixed $field
	 */
	public function setGetField( $field ): void
	{
		$this->getField = $field;
	}

	/**
	 * 获取全部数据列表
	 * @param array $where
	 * @param string $order
	 * @param string $sort
	 * @return array
	 */
	public function getAll( $where = [] , $order = 'id' , $sort = 'desc' )
	{
		try {
			return $this->where ( $where )->field ( $this->getField )->order ( $order . ' ' . $sort )->select ();
		}
		catch ( \Exception $e ) {
			$msg = $e->getMessage ();
			return [];
		}
	}

	/**
	 * 获取分页列表
	 * @param array $where
	 * @param string $order
	 * @param string $sort
	 * @param int $pageRows
	 * @return array|null
	 */
	public function getList( $where = [] , $order = 'id' , $sort = 'desc' , $pageRows = 10 )
	{
		try {
			$count = $this->getCount ( $where );
			$Page  = new \Think\Page( $count , $pageRows );//实例化分页类 传入总记录数和每页显示的记录数(25)
			$show  = $Page->show ();//分页显示输出
			//进行分页数据查询 注意limit方法的参数要使用Page类的属性
			$list = $this->where ( $where )->order ( $order . ' ' . $sort )->field ( $this->getField )->limit ( $Page->firstRow . ',' . $Page->listRows )->select ();
			$data = [ 'page' => $show , 'list' => $list ];
			return $data;
		}
		catch ( \Exception $e ) {
			var_dump ( $e->getMessage () );
			return null;
		}
	}

	/**
	 * 获取数量统计
	 * @param $where
	 * @return null
	 */
	public function getCount( $where )
	{
		try {
			return $this->where ( $where )->count ();
		}
		catch ( \Exception $e ) {
			return null;
		}
	}

	/**
	 * 获取某一个符合条件的记录
	 * @param $where
	 * @return array|mixed
	 */
	public function getOne( $where )
	{
		try {
			return $this->where ( $where )->field ( $this->getField )->find ();
		}
		catch ( \Exception $e ) {
			return [];
		}
	}

	/**
	 * 添加一个记录
	 * @param $data
	 * @return bool|mixed
	 */
	public function addOne( $data )
	{
		try {
			return $this->data ( $data )->add ();
		}
		catch ( \Exception $e ) {
			return false;
		}
	}

	/**
	 * 修改一条记录
	 * @param $where
	 * @param $data
	 * @return bool
	 */
	public function saveOne( $where , $data )
	{
		try {
			return $this->where ( $where )->save ( $data );
		}
		catch ( \Exception $e ) {
			return false;
		}
	}
}