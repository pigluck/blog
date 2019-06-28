<?php
/**
 * Created by Sublime Text.
 * User: LuckyPig
 */
namespace app\admin\controller;
use think\Db;

/**
 * 
 */
class Index extends Common{
	public function _initialize(){
        parent::_initialize();
    }

    public function index(){
//        $res = db('auth_rule')->column('id');
//        foreach ($res as $key => $value) {
//            $ress .= $value.',';
//        }
        $authRule = db('auth_rule')->where('menustatus=1')->order('sort')->select();
        $menus = array();
        foreach ($authRule as $key=>$val){
            switch ($val['title']){
                case 'HTML':
                    $authRule[$key]['href'] = url($val['href'],['type'=>'html']);
                    break;
                case 'CSS':
                    $authRule[$key]['href'] = url($val['href'],['type'=>'css']);
                    break;
                case 'JavaScript':
                    $authRule[$key]['href'] = url($val['href'],['type'=>'js']);
                    break;
                default:
                    $authRule[$key]['href'] = url($val['href']);
                    break;
            }


            if($val['pid']==0){
                if(session('aid')!=1){
                    if(in_array($val['id'],$this->adminRules)){
                        $menus[] = $val;
                    }
                }else{
                    $menus[] = $val;
                }
            }
        }
        foreach ($menus as $k=>$v){
            foreach ($authRule as $kk=>$vv){
                if($v['id']==$vv['pid']){
                    if(session('aid')!=1) {
                        if (in_array($vv['id'], $this->adminRules)) {
                            $menus[$k]['children'][] = $vv;
                        }
                    }else{
                        $menus[$k]['children'][] = $vv;
                    }
                }
            }
        }
//        dump($menus[0]['children']);die();
        $this->assign('menus', $menus);
    	return $this->fetch();
    }

    public function loginOut(){
		
        parent::log(request(),session('admin').'退出了后台管理');
        session('admin', null);
		$this->redirect('/login');
	}

    // public function about(){
    //     $content = db('admin')->where('admin_id',1)->value('content');
    //     $this->assign('content',$content);
    //     return $this->fetch();
    // }

    public function save(){
        $req = request()->post();
        unset($req['file']);
        // dump($req);die();
        if (empty($req)) {
            echo json_encode(["code"=>"1001","msg"=>"缺少参数"]);
            die();
        }
        $res = db('admin')->update($req);
        if ($res) {
            parent::log(request(),'修改了管理员个人信息');
            echo json_encode(["code"=>"0000","msg"=>"修改成功"]);
        }else{
            echo json_encode(["code"=>"1001","msg"=>"修改失败"]);
        }
    }

    public function main(){
    	$stime = microtime(true);
    	$username = session('admin');
    	//获取文章，栏目，友链条数
    	$article = db('article')->where('delete_time',0)->count();
    	$category = db('category')->count();
    	$link = db('flink')->count();
      	$message = db('comment')->count();
    	$login = db('login')->where('username',$username)->count();
    	$admin = db('admin')->count();
    	$num = array('article' =>$article ,'category'=>$category,'link'=>$link,'login'=>$login,'admin'=>$admin);
    	//获取管理员登录信息
    	$loginInfo = db('login')->where('username',$username)->field('ip,create_time')->order('create_time desc')->limit(1,1)->select();
        if (empty($loginInfo)) {
            $loginInfo[0]['create_time'] = null;
        }
    	//获取系统信息
    	$version = Db::query('SELECT VERSION() AS ver');
        $config  = [
            'url'             => $_SERVER['HTTP_HOST'],
            'document_root'   => $_SERVER['DOCUMENT_ROOT'],
            'server_os'       => PHP_OS,
            'server_port'     => $_SERVER['SERVER_PORT'],
            'server_ip'       => php_sapi_name(),
            'server_soft'     => $_SERVER['SERVER_SOFTWARE'],
            'php_version'     => PHP_VERSION,
            'mysql_version'   => $version[0]['ver'],
            'max_upload_size' => ini_get('upload_max_filesize')
        ];
      $etime = microtime(true);
    	$this->assign([
    		'info'=>$loginInfo[0],
    		'num'=>$num,
    		'sys'=>$config,
    		'time'=>$etime-$stime,
          	'message'=>$message
    	]);
    	return $this->fetch();
    }

    public function user(){
    	return $this->fetch();
    }

    public function upload(){

		$file = request()->file('file');
		
		if (!empty($file)) {
			$info = $file->rule('date')->move(ROOT_PATH . 'public' . DS . 'uploads');
			if ($info) {
				$thumb = '/uploads/'.$info->getSaveName();
				echo json_encode(["code"=>"00000","msg"=>$thumb]);
			}else{
				echo json_encode(["code"=>"10002","msg"=>$file->getError()]);	
			}
		}else{
			echo json_encode(["code"=>"10001","msg"=>"缺少请求文件"]);
		}
	}

    public function clearcache(){
        $R = RUNTIME_PATH;
        $this->_deleteDir($R);
        parent::log(request(),'清除了缓存');
    }

    private function _deleteDir($R)
    {
        $handle = opendir($R);
        while (($item = readdir($handle)) !== false) {
            if ($item != '.' and $item != '..') {
                if (is_dir($R . '/' . $item)) {
                    $this->_deleteDir($R . '/' . $item);
                } else {
                    if (!unlink($R . '/' . $item))
                        die('error!');
                }
            }
        }
        closedir($handle);
        return rmdir($R);
    }
  
  public function zxt(){
        $start = strtotime('this week Monday',time());
       $res = db('fwl')->where('time','>',$start)->field('zl,xzfk,xzyh')->select();
    $data = array();
        foreach ($res as $key => $value) {
            $data['zl'][] = $value['zl'];
            $data['xzfk'][] = $value['xzfk'];
            $data['xzyh'][] = $value['xzyh'];
        }
        return json_encode(['zl'=>$data['zl'],'xzfk'=>$data['xzfk'],'xzyh'=>$data['xzyh']]);
    
    }
	
}