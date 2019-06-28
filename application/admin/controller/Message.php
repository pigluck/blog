<?php
/**
 * Created by Sublime Text.
 * User: LuckyPig
 */
namespace app\admin\controller;

 class Message extends Common{
 	public function _initialize(){
        parent::_initialize();
    }

    public function index(){
    	return $this->fetch();
    }

    public function list(){
    	$list = db('comment')->alias('c')->join('user u','c.uid = u.uid')->field('u.username,c.aid,c.create_time,c.content,c.id')->paginate(10);
    	$messNum = db('comment')->count();

    	return $result = ['code'=>0,'msg'=>'获取成功!','data'=>$list->toArray()['data'],'count'=>$messNum];
    }

    public function delete(){
        $id = request()->post()['id'];
        $count = db('comment')->where('id',$id)->whereOr('parentid',$id)->delete();
        if ($count) {
            parent::log(request(),'删除id为'.$id.'的评论及其子评论');
            echo json_encode(["code"=>"0000","msg"=>"删除成功"]);
        }else{
            echo json_encode(["code"=>"0010","msg"=>"删除失败"]);
        }
    }
 }