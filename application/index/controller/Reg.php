<?php
namespace app\index\controller;
use think\Controller; 
use think\Request;
use think\Db;
use think\Cookie;
use app\index\model\User;
use think\Loader;
/**
 * 
 */
class Reg extends controller{
    private function reginit()
    {
       if(cookie('username')||cookie('uid')){
       	$this->redirect('/');
       }
    }
	public function index(){
      $this->reginit();
		return $this->fetch();
	}

	public function userLogin(){
      $this->reginit();
		return $this->fetch();
	}

	public function user(){
		$username = request()->param()['username'];
		$res = db('user')->where('username',$username)->find();
		if (empty($res)) {
			return true;
		}else{
			return false;
		}
	}

	public function sendsms(){
		$phone = request()->param()['tel'];
		$code = rand_string(6,1);
		session([
		    'prefix'     => 'module',
		    'type'       => '',
		    'auto_start' => true,
		    'expire' => 60*5,
		]);
		session($phone,$code);
		$content = "【LuckyPig】你正在进行luckypig账号注册操作，".$code."为你的验证码，5分钟内有效。如非本人操作请忽略。";
		// return $content."----".$phone;
		$res = sms($content,$phone);
		if ($res == "0") {
			return "短信发送成功";
		}else{
			return "短信发送失败";
		}

	}

	public function checkCode(){
		$req = request()->param();
		$code = $req['smscode'];
		session([
		    'prefix'     => 'module',
		    'type'       => '',
		    'auto_start' => true,
		]);
		if ($code != session($req['tel'])) {
			return json_encode(['msg'=>'验证码错误','code'=>'01']);
		}else{
			session($req['tel'],null);
			unset($req['smscode']);
			$req['create_time'] = time();
			$req['uid'] = rand_string(10,1);
			$req['logo'] = "/static/index/images/face.gif";
			db('user')->insert($req);
			Cookie::set('username',$req['username'],60*60*24*7);
			Cookie::set('uid',$req['uid'],60*60*24*7);
          	$content = "【LuckyPig】你有新的用户注册，用户ID为".$req['uid'].",注册时间为".date("Y-m-d H:i").",请到后台查看。";
			
			sms($content,"15172462327");
			$this->log(request(),$req['uid'],$req['username'],5);
$this->fwl('xzyh');
			return json_encode(['msg'=>'注册成功','code'=>'00']);
			// $this->redirect('/index');
		}
		// dump($req);
		// return json_encode($req);
	}

	public function logincheckuser(){
		$username = request()->param()['username'];
		$res = db('user')->where('username',$username)->find();
		if (empty($res)) {
			return false;
		}else{
			return true;
		}
	}

	public function checklogin(){
		$req = request()->param();
		$pwd = db('user')->where('username',$req['username'])->value('password');
		if ($pwd != $req['password']) {
			return json_encode(['msg'=>'密码错误','code'=>'111']);
		}else{
			$uid = db('user')->where('username',$req['username'])->value('uid');
			if ($req['rem'] == 1) {
				Cookie::set('username',$req['username'],60*60*24*7);
				Cookie::set('uid',$uid,60*60*24*7);
			}else{
				Cookie::set('uid',$uid);
				Cookie::set('username',$req['username']);
			}
			$user = User::get(function($query) use ($uid){
				$query->where('uid',$uid);
				});
			
			$user->exp = $user->exp+5;
			$user->save();

			$this->log(request(),$uid,$req['username'],3);
$this->fwl('zl');
			// $logCount = db('log')->alias('l')->join('user u','l.uid = u.id')->where('l.ip',request()->ip())->where('l.uid',$uid)->where('operation',3)->select();
			//$logCount = db('log')->where('uid',$uid)->where('operation',3)->where('ip',request()->ip())->count();
			//if ($logCount < 3) {
			//	$tel = db('user')->where('uid',$uid)->value('tel');
			//	if (!empty($tel)) {
			//		$content = "【LuckyPig】你的LuckyPig账号:".$req['username'].",存在异常登录，如非本人操作，请及时更换密码。";
					//sms($content,$tel);
				//}
			//}
			// dump($content.$tel);die();
			return json_encode(['msg'=>'登录成功','code'=>'00']);
		}
		
	}

	public function logout(){
		Cookie::delete('username');
		Cookie::delete('uid');
		$this->redirect('/');
	}

    private function log($Request,$uid,$username,$operation){
           
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
                'city' => $city,
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