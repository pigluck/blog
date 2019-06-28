<?php
namespace app\index\controller;
use think\Controller;
use think\Loader;
use app\index\model\User;

/**
 * 
 */
class Login extends controller{
    public function _initialize()
    {
       if(cookie('username')||cookie('uid')){
       	$this->redirect('/');
       }
    }
	public function sina(){
    $callback_url = "http://www.luckypigblog.com/index/login/sina_callback";//回调地址,必须是提交网站域名下的某一个url
    $obj = new \SaeTOAuthV2('2539693553', 'fb055160b055481a3964a3de7da91d93');//$client_id就是App Key  $client_secret就是App Secret
    $weibo_login_url = $obj->getAuthorizeURL($callback_url);
    $this->redirect($weibo_login_url);

}

public function sina_callback(){
	$code = request()->param()['code'];
	$keys['code'] = $code;
	$keys['redirect_uri'] = "http://www.luckypigblog.com/index/login/sina_callback_ba";
    $obj = new \SaeTOAuthV2('2539693553', 'fb055160b055481a3964a3de7da91d93');//$client_id就是App Key  $client_secret就是App Secret
    $a = $obj->getAccessToken($keys);
    cookie('sina_token',$a['access_token']);
    // dump($auth);

// $info = file_get_contents("https://api.weibo.com/2/users/show.json?access_token={$a['access_token']}&uid={$a['uid']}");
    $o = new \SaeTClientV2('2539693553', 'fb055160b055481a3964a3de7da91d93',$a['access_token']);
    $res = $o->show_user_by_id($a['uid']);
    // dump($res);die();
    $info = db('user')->where('sid',$res['idstr'])->find();
    if ($info) {
      cookie('uid',$info['uid']);
    	cookie('username',$info['username']);
      $this->user();
    }else{
      $img = file_get_contents($res['profile_image_url']);
      $path = 'static/index/userImg/'.rand_string(10).'.jpg';
      file_put_contents($path,$img);
     $uid = rand_string(10,1);
    	 $data = [
    	 	'sid'=>$res['idstr'],
    	 	'create_time'=>time(),
    	 	'username'=>$res['name'],
            'uid'=>$uid,
            'logo'=>'/'.$path
    	 ];
    	 db('user')->insert($data);
    	cookie('username',$res['name']);
      cookie('uid',$uid);
    }
    return $this->fetch('reg/qqlogin');
}

 public function qqlogin(){
   
 	Loader::import('qq.Oauthqq', EXTEND_PATH,'.class.php');
 	$oauth = new \Oauthqq();
 	$url = $oauth->qq_login();
    $this->redirect($url);
 }

 public function qq(){
 	$code = request()->param();
 	Loader::import('qq.Oauthqq', EXTEND_PATH,'.class.php');
 	$oauth = new \Oauthqq();
 	$access_token = $oauth->qq_callback($code['state']);
 	$openId = $oauth->get_openid();
 	Loader::import('qq.QC', EXTEND_PATH,'.class.php');
 	$qc = new \QC($access_token,$openId);
 	$userInfo = $qc->get_user_info();
  
    $info = db('user')->where('qid',$openId)->find();
    if ($info) {
      cookie('uid',$info['uid']);
    	cookie('username',$info['username']);
      $this->user();
    }else{
      $img = file_get_contents($userInfo['figureurl_1']);
      $path = 'static/index/userImg/'.rand_string(10).'.jpg';
      file_put_contents($path,$img);
      $uid = rand_string(10,1);
      
    	 $data = [
    	 	'qid'=>$openId,
    	 	'create_time'=>time(),
    	 	'username'=>$userInfo['nickname'],
            'uid'=>$uid,
            'logo'=>'/'.$path
    	 ];
    	 db('user')->insert($data);
    	cookie('username',$userInfo['nickname']);
      cookie('uid',$uid);
    }
    return $this->fetch('reg/qqlogin');
 }

 private function user(){
    if (!empty(cookie('uid'))) {
        $user = User::get(function($query){
        $query->where('uid',cookie('uid'));
        });
                
        $user->credit = $user->credit+5;
        $user->exp = $user->exp+20;
        $user->save();
        }
    }
}