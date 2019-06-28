<?php
namespace app\index\controller;
use think\Controller;

/**
 * 
 */
class Findpwd extends controller{
	public function index(){
	    //用户找回密码标识符
	    $state = rand_string(4,1);
	    //找回密码第一步
	    session($state,'1');
	    $this->assign('state',$state);
		return $this->fetch();
	}

	public function findstept(){
	    $req = request()->param();
	    $username = $req['username'];
	    $state = $req['step'];
	    if (empty(session($state))){
	        return json_encode(['code'=>'0011','msg'=>'请先进行第一步，验证用户名']);
        }else{
            session($state,null);
	        session($state,$username);
	        $tel = db('user')->where('username',$req['username'])->value('tel');
	        if (empty($tel)){$tel = '';}
	        return json_encode(['code'=>'00','msg'=>$req['step'],'tel'=>$tel]);
        }
    }

    public function findcheckCode(){
        $req = request()->param();
	    if (empty(session($req['state']))){return json_encode(['msg'=>'请先进行第一步','code'=>'01']);}

        $code = $req['smscode'];

        if ($code != session($req['tel'])) {
            return json_encode(['msg'=>'验证码错误','code'=>'01']);
        }else{
            session($req['tel'],null);
          
            unset($req['smscode']);
//
           $res =  db('user')->where('username',session($req['state']))->update(['tel'=>$req['tel'],'password'=>$req['password']]);
            if ($res) {
                session($req['state'],null);
                return json_encode(['msg'=>'修改成功','code'=>'00']);
            }
            return json_encode(['msg'=>'修改失败','code'=>'10']);

        }
    }

    public function sendsms(){
        $phone = request()->param()['tel'];
        $code = rand_string(6,1);
        session([
            'prefix'=>'think',
            'type'       => '',
            'auto_start' => true,
            'expire' => 60*5,
        ]);
        session($phone,$code);
        $content = "【LuckyPig】你正在进行luckypig密码找回操作，".$code."为你的验证码，5分钟内有效。如非本人操作请忽略。";
        // return $content."----".$phone;
        $res = sms($content,$phone);
        if ($res == "0") {
            return "短信发送成功";
        }else{
            return "短信发送失败";
        }
//        return session($phone).'-----'.$phone;
    }
}