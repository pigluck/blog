<?php
/**
 * Created by Sublime Text.
 * User: LuckyPig
 */
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Loader;
use think\Cookie;

class Common extends controller{
    public function _initialize(){
    	$sys = db('system')->field('webname,keywords,description,bah,copyright,url,logo')->find();
    	$this->assign('sys',$sys);
    	$cat = db('category')->field('id,catname,catdir,parentid')->select();
    	$catlist = catsort($cat);
    	$this->assign('catlist',$catlist);
    	$key = db('article')->where('delete_time',0)->field('tag')->select();
    	$arr = [];
    	for($i = 0;$i<count($key);$i++){
    		$str = $key[$i]['tag'];
    		$strarr = explode(',',$str);
    		$arr = array_merge($arr,$strarr);
    		$strarr = null;
    	}
    	$this->assign('tag',array_filter(array_unique($arr)));
    	$link = db('flink')->field('name,url')->select();
    	$this->assign('link',$link);
        $request = Request::instance();
        $action = $request->action();
        $controller = $request->controller();
        $this->assign('action',($action));
        $this->assign('controller',strtolower($controller));
        define('MODULE_NAME',strtolower($controller));
        define('ACTION_NAME',strtolower($action));
        $username = cookie('username');
        if (empty($username) || empty(cookie('uid'))) {
            $this->assign('username','');
        }else{
            $this->assign('username',$username);
          	$userinfo = db('user')->where('uid',cookie('uid'))->find();
            $this->assign('userinfo',$userinfo);
        }
        $lang = cookie('lang')?:'zh-cn';
        $this->assign('lang',$lang);
        if (empty(cookie('uid')) || empty(cookie('username'))) {
            if (empty(cookie('LUCKYPIGID'))) {
                $uid = rand_string(10,1);
                // cookie('LUCKYPIGID',$uid,3600);
                Cookie::forever('LUCKYPIGID',$uid);
                cookie('USERID',$uid);
                $this->log(request(),$uid,'游客',6);
$this->fwl('xzfk,zl');
            }else{
               cookie('USERID',cookie('LUCKYPIGID')); 
$this->log(request(),cookie('LUCKYPIGID'),'游客',7);
$this->fwl('zl');
            }
            
        }else{
            cookie('USERID',cookie('uid'));
if (empty(cookie('log'))) {
                $this->log(request(),cookie('uid'),cookie('username'),3);
$this->fwl('zl');
                cookie('log',time());
            }
        }
        

    }

    public function _empty(){
        // return $this->error('空操作，返回上次访问页面中...');
        return $this->fetch('exception_html/404');
    }

    protected function log($Request,$uid,$username,$operation){
 $res = getCity($Request->ip());
        if ($res->status == 1) {
            Loader::import('ip.IpLocation', EXTEND_PATH,'.class.php');
            $Ip = new \IpLocation('UTFWry.dat');
            $city = $Ip->getlocation($Request->ip());
        }else{
            $city = $res->content->address;
        } 
            $log = [
                'uid' => $uid,
                'username' => $username,
                'ip' => $Request->ip(),
                'city' =>$city,
                'operation' => $operation,
                'create_time' => time()
            ];
            db('log')->insert($log);
        
    }

    protected function fwl($field){
$res = db('fwl')->where('date',date('Y-m-d',time()))->find();
        if (empty($res)) {
            db('fwl')->insert(['time'=>time(),'date'=>date('Y-m-d',time())]);
        }
        $arr = explode(',',$field);
        for($i = 0;$i<count($arr);$i++){
            db('fwl')->where('date',date('Y-m-d',time()))->setInc($arr[$i]);
        }
    }
}
