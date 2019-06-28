<?php
/**
 * Created by Sublime Text.
 * User: LuckyPig
 */
 namespace app\admin\controller;
 use app\admin\controller\Common;

 /**
  * 
  */
 class Vip extends Common{
 	public function index(){
 		return $this->fetch();
 	}

 	public function viplist(){
 		$list = db('user')->order('id desc')->paginate(10);
		$vipNum = db('user')->count();
	
       return $result = ['code'=>0,'msg'=>'获取成功!','data'=>$list->toArray()['data'],'count'=>$vipNum];
 	}

 	public function edit(){
		$req = request()->param();
		$id = $req['id'];
		$info = db('user')->where('id',$id)->field('id,uid,tel,logo,username,credit,exp,grade,create_time')->find();
		if (empty($info)) {
			return false;
		}
		echo json_encode($info);
	}

	public function add(){
		return $this->fetch();
	}

	public function save(){
		$req = request()->param();
		if (empty($req)) {
            echo json_encode(["code"=>"1001","msg"=>"缺少参数"]);
            die();
        }
        $res = db('user')->update($req);
        if ($res) {
            parent::log(request(),'修改了用户id为'.$req['id'].'的个人信息');
            echo json_encode(["code"=>"0000","msg"=>"修改成功"]);
        }else{
            echo json_encode(["code"=>"1001","msg"=>"修改失败"]);
        }
	}

	public function delete(){
		$req = request()->param();
		if (empty($req)) {
            echo json_encode(["code"=>"1001","msg"=>"缺少参数"]);
            die();
        }
        $res = db('user')->delete($req['id']);
        if ($res) {
            parent::log(request(),'删除了id为'.$req['id'].'的用户账号');
            echo json_encode(["code"=>"0000","msg"=>"修改成功"]);
        }else{
            echo json_encode(["code"=>"1001","msg"=>"修改失败"]);
        }
	}

	public function reset(){
		$req = request()->param();
		if (empty($req)) {
            echo json_encode(["code"=>"1001","msg"=>"缺少参数"]);
            die();
        }
        $res = db('user')->where('id',$req['id'])->update(['password'=>md5('123456')]);
        if ($res) {
            parent::log(request(),'重置了用户id为'.$req['id'].'的密码');
            echo json_encode(["code"=>"0000","msg"=>"重置成功"]);
        }else{
            echo json_encode(["code"=>"1001","msg"=>"重置失败或已经重置"]);
        }
	}
 }