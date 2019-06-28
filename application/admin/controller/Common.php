<?php
/**
 * Created by Sublime Text.
 * User: LuckyPig
 */
namespace app\admin\controller;
use app\admin\model\Login;
use think\Controller;
use think\Session;
use think\Db;

class Common extends controller{
	public function _initialize(){
		if (!session('admin')) {
			$this->redirect('/login');
		}
      define('MODULE_NAME',strtolower(request()->controller()));
        define('ACTION_NAME',strtolower(request()->action()));
      if(session('aid')!=1){
            $this->HrefId = db('auth_rule')->where('href','admin/'.MODULE_NAME.'/'.ACTION_NAME)->value('id');
            //当前管理员权限
            $map['admin_id'] = session('aid');
            $rules=Db::table(config('database.prefix').'admin')
                ->where($map)
                ->value('rules');
            $this->adminRules = explode(',',$rules);
//            dump($this->HrefId);die();
            if($this->HrefId){
                if(!in_array($this->HrefId,$this->adminRules)){
                    $this->error('您无此操作权限');
                }
            }
        }
		$adminInfo = db('admin')->where('username',session('admin'))->field('admin_id,username,realname,tel,email,pic')->find();

		$this->assign('adminInfo',$adminInfo);
		// $this->assign('list',$list);
		
		
	}

	protected function log($req,$operation,$username = ''){
		$username = empty($username)?session('admin'):$username;
		$login = new Login();
	    $login->data([
	    'username'=>$username,
	    'ip'=>$req->ip(),
	    'operation'=>$operation
	    ]);
	    $login->save();
	}
}