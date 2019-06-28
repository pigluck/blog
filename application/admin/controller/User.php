<?php
/**
 * Created by Sublime Text.
 * User: LuckyPig
 */
namespace app\admin\controller;

class User extends Common{
	public function _initialize(){
        parent::_initialize();
    }

    public function index(){
    	return $this->fetch();
    }

    public function changepwd(){
        $id = db('admin')->where('username',session('admin'))->value('admin_id');
        $this->assign('id',$id);
        return $this->fetch();
    }

    public function updatepwd(){
        $req = request()->post();
        $oldpwd = $req['oldpwd'];
        $old = db('admin')->where('username',$req['username'])->value('pwd');
        if ($old != $oldpwd) {
           echo json_encode(["code"=>"1000","msg"=>"原密码错误"]);
           die();
        }
        unset($req['oldpwd']);
        $res = db('admin')->update($req);
        if ($res) {
            session('admin', null);
            parent::log(request(),'修改了管理员登录密码');
           echo json_encode(["code"=>"0000","msg"=>"修改成功"]);
        }else{
            echo json_encode(["code"=>"1001","msg"=>"修改出错"]);
            // dump($req);
        }
    }
  
}