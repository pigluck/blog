<?php
/**
 * Created by Sublime Text.
 * User: LuckyPig
 */
namespace app\admin\controller;


class Setting extends Common{
	public function _initialize(){
        parent::_initialize();
    }

    public function index(){
    	$config = db('system')->find();
    	$this->assign('sys',$config);
    	return $this->fetch();
    }

    public function edit(){
    	$req = request()->post();
        unset($req['file']);
    	$res = db('system')->update($req);
    	if ($res) {
            parent::log(request(),'修改了系统设置');
    		echo json_encode(["code"=>"0000","msg"=>"修改成功"]);
    	}else{
    		echo json_encode(["code"=>"1000","msg"=>"修改出错"]);
    	}
    }
}