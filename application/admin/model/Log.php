<?php
namespace app\admin\model;
use think\Model;

class Log extends Model{
	protected $autoWriteTimestamp = true;
	protected $updateTime = false;

	public function getStatusAttr($value)
   	 {
        	$status = [1=>'发布评论',2=>'回复评论',3=>'登录',4=>'点击文章',5=>'注册'];
        	return $status[$value];
    	}
}