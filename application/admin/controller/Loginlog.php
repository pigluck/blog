<?php
/**
 * Created by Sublime Text.
 * User: LuckyPig
 */
namespace app\admin\controller;
use app\admin\model\Login;
use app\admin\model\Log;

class Loginlog extends Common{
	public function _initialize(){
        parent::_initialize();
    }

    public function index(){
    	
    	return $this->fetch();
    }

    public function login(){
        // $login = db('login')->field('id,username,ip,status,create_time')->order('create_time desc')->paginate(8);
        $logincount = db('login')->count();
        // $page = $login->render();
        $login = Login::where(true)->order('create_time desc')->paginate(10);
        return $result = ['code'=>0,'msg'=>'获取成功!','data'=>$login->toArray()['data'],'count'=>$logincount];
    }

public function user(){
return $this->fetch();
        $logincount = db('log')->count();
        $login = Log::where(true)->order('create_time desc')->paginate(10);
//dump($login->toArray()['data']);die();
        return $result = ['code'=>0,'msg'=>'获取成功!','data'=>$login->toArray()['data'],'count'=>$logincount];
    }

public function userlogin(){

        $logincount = db('log')->count();
        $login = Log::where(true)->order('create_time desc')->paginate(10);

        return $result = ['code'=>0,'msg'=>'获取成功!','data'=>$login->toArray()['data'],'count'=>$logincount];
    }

    public function excelExport(){
        $name="操作日志";
        $header=['用户名','IP地址','操作','时间'];
        $res = Login::all(function($query){
            $query->field('username,ip,operation,create_time')->order('id desc');
        });
        foreach ($res as $key => $value) {
            $data[] = $value->toArray();
        }
        parent::log(request(),'导出了管理员操作日志');
       xlsExport($name,$header,$data);
       
    }
}