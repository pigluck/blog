<?php
/**
 * Created by Sublime Text.
 * User: LuckyPig
 */
namespace app\admin\controller;
use app\admin\model\Flink;

class Link extends Common{
	public function _initialize(){
        parent::_initialize();
    }

    public function index(){
    	$flist = db('flink')->field('link_id,name,url')->select();
    	$this->assign('flist',$flist);
    	// dump($list);
    	return $this->fetch();
    }

    public function delete(){
    	$req = request()->param();
    	$id = $req['id'];
    	if (empty($id)) {
    		return json_encode(["code"=>"2000","msg"=>"缺少请求参数"]);
    	}
    	$fid = explode(',',$id);
    	$res = db('flink')->delete($fid);
    	if ($res) {
            parent::log(request(),'删除了一条友情链接');
    		echo json_encode(["code"=>"0000","msg"=>"删除成功"]);
    	}else{
    		echo json_encode(["code"=>"1001","msg"=>"删除失败"]);
    	}
    }

    public function save(){
    	$req = request()->param();
    	if (empty($req['link_id'])) {
    		unset($req['link_id']);
    	}
    	$list = [$req];
    	$link = new Flink();
    	$res = $link->saveAll($list);
    	// dump($req);
    	// $res = false;
    	if ($res) {
            parent::log(request(),'修改了友情链接信息');
    		echo json_encode(["code"=>"0000","msg"=>"添加成功"]);
    	}else{
    		echo json_encode(["code"=>"1000","msg"=>"添加失败"]);
    	}
    }

    public function edit(){
    	$id = request()->param()['id'];
    	$info = db('flink')->where('link_id',$id)->find();
    	return $info;
    }
}