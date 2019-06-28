<?php
namespace app\index\model;
use think\Model;

/**
 * 
 */
class User extends Model{
	protected static function init(){

		User::afterUpdate(function($user){
			
			$exp = 50;
			$grade = floor($user->exp/$exp);
			User::where('uid',$user->uid)->update(['grade'=>$grade]);
		});
	}
	
}