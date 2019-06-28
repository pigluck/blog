<?php
/**
 * Created by Sublime Text.
 * User: LuckyPig
 */
namespace app\admin\controller;
use app\admin\model\Login;
use think\Controller;
use think\Request;
use think\Session;
use think\Db;
use think\Loader;

class Adminlogin extends Controller{
  public function _initialize(){
        if (session('admin')) {
            $this->redirect('/admin');
        }
    }

  public function index(){
    return $this->fetch();
  }

  public function login(Request $request){
    $req = Request::instance();
    $username = $req->param()['username'];
    $password = $req->param()['pwd'];
    $captcha = $req->param()['code'];
    if (!captcha_check($captcha)) {
    	echo json_encode(['status'=>'Erro','msg'=>'验证码错误']);
    	die();
    }

    $res = db('admin')->where('username',$username)->field('pwd,admin_id')->find();
    $pwd = $res['pwd'];

    if (empty($pwd) || $pwd != $password) {
      echo json_encode(["status"=>"Erro","msg"=>"登录失败，账号或密码错误"]);
    }else{
      $login = new Login();
      $login->data([
        'username'=>$username,
        'ip'=>$req->ip(),
        'operation'=>$username.'登录到后台'
      ]);
      $login->save();
      session('admin',$username);
      session('aid',$res['admin_id']);
      echo json_encode(["status"=>"ok","msg"=>"登录成功"]);
    }
    
  }
  
  
}