<?php
namespace app\index\model;
use think\Model;
use app\index\model\User;

/**
 * 
 */
class Article extends Model{
	
	protected static function init(){

		Article::afterUpdate(function($article){
			if (!empty(cookie('uid'))) {
				$user = User::get(function($query){
				$query->where('uid',cookie('uid'));
				});
				
				$user->credit = $user->credit+5;
				$user->exp = $user->exp+10;
				$user->save();
			}
		});
	}
}