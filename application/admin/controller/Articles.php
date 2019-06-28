<?php
/**
 * Created by Sublime Text.
 * User: LuckyPig
 */
namespace app\admin\controller;
use app\admin\model\Article;
use think\Request;
use think\Db;

/**
 * 
 */
class Articles extends Common{
	public function _initialize(){
        parent::_initialize();
    }
	
	public function index(){
        // $articleNum = db('article')->where('delete_time',0)->count();
        
		return $this->fetch();
	}

	public function newslist(){
		$list = db('article')->alias('a')->join('category c','a.cid = c.id')->where('delete_time',0)->field('a.id,a.title,c.catname,a.keywords,a.hits,a.update_time,a.posid,a.tj')->order('a.update_time desc')->paginate(8);
		$articleNum = db('article')->where('delete_time',0)->count();
	
       return $result = ['code'=>0,'msg'=>'获取成功!','data'=>$list->toArray()['data'],'count'=>$articleNum];
		
	}

	public function delete(Request $request){
		$req = Request::instance();
		$id = $req->param()['id'];
		$rid = explode(',',$id);
		if (empty($rid)) {
			echo json_encode(['code'=>'10001','msg'=>'缺少请求参数']);
			die();
		}
		
		$res = Article::destroy($rid);
		
		if ($res==0) {
			echo json_encode(['code'=>'10002','msg'=>'删除0条数据']);
		}else{
			for ($i=0 ;$i<count($rid);$i++) {
				db('article',[],false)->alias('a')->join('category c','a.cid = c.id')->where('a.id',$rid[$i])->setDec('c.num');
			}
			parent::log($req,'删除'.count($rid).'篇文章');
			echo json_encode(["code"=>"1","msg"=>"删除成功"]);
		}
	}

	public function add(){
		$cat = db('category')->where('lx',1)->where('open',1)->field('catname,id')->order('id asc')->select();
		$this->assign('cat',$cat);
		$files = dir_list(APP_PATH.'index/view/index','html');
    	$templates = array();
        foreach ($files as $key=>$file){
            $filename = basename($file);
            $templates[$key]['value'] =  substr($filename,0,strrpos($filename, '.'));
            $templates[$key]['filename'] = $filename;
            $templates[$key]['filepath'] = $file;
            $templates[$key]['filesize']=byte_format(filesize($file));
            $templates[$key]['filemtime']=filemtime($file);
            $templates[$key]['ext'] = strtolower(substr($filename,strrpos($filename, '.')-strlen($filename)));
        }
        $this->assign ( 'templates',$templates );
        $lang = dir_list(APP_PATH.'index/lang','php');
        $language = [];
        foreach ($lang as $key => $value) {
        	$language[] =basename($value,'.php');
        }
        $langres = db('lang')->where('en','in',$language)->field('en,zh')->order('id desc')->select();
       $this->assign('lang',$langres);
		return $this->fetch();
	}

	public function upload(){
		$file = Request::instance()->file('file');
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

	public function save(){
		$req = request()->param();
		$flag=true;
		if (empty($req['id'])) {
			unset($req['id']);
			$flag=false;
		}
		$req['tj'] =empty($req['tj'])?0:1;
		$req['posid'] =empty($req['posid'])?0:1;
      $req['writer']  = session('admin');
		$article = new Article();
		if ($flag) {
			db('article')->alias('a')->join('category c','a.cid = c.id')->where('a.id',$req['id'])->setDec('c.num');
			
		}
		$res = $article->saveAll([$req]);
		if ($res) {
			db('category')->where('id',$req['cid'])->setInc('num');
			if ($flag) {
				parent::log(request(),'修改id为'.$req['id'].'的文章');
			}else{
				parent::log(request(),'新发布一篇文章');
			}
			
			echo json_encode(["code"=>"0000","msg"=>"发布成功"]);
		}else{
			echo json_encode(["code"=>"1001","msg"=>"发布失败"]);
		}
	}

	public function edit(){
		$req = request()->param();
		$id = $req['id'];
		$info = db('article')->where('id',$id)->field('id,title,tag,cid,keywords,description,content,thumb,posid,tj,template')->find();
		if (empty($info)) {
			return false;
		}
		// $cat = db('category')->field('catname,id')->order('id asc')->select();
		// $this->assign('info',$info);
		// $this->assign('cat',$cat);
		// $ishtml = ["code"=>"1","msg"=>"已发布"];
		// $this->assign('ishtml',$ishtml);
		echo json_encode($info);
	}

	public function change(){
		$req = request()->post();
		$id = $req['id'];
		$field = $req['name'];
		$value = $req['value'];
		$res = db('article')->where('id',$id)->update([$field=>$value]);
		if ($res) {
			echo json_encode(["code"=>"1","msg"=>"修改成功"]);
		}else{
			echo json_encode(["code"=>"0001","msg"=>"修改失败"]);
		}
	}

	public function trash(){
		return $this->fetch();
	}

	public function trashlist(){
		$list = db('article')->alias('a')->join('category c','a.cid = c.id')->where('delete_time','neq',0)->field('a.id,a.title,c.catname,a.delete_time')->order('a.update_time desc')->paginate(8);
		$articleNum = db('article')->where('delete_time','neq',0)->count();
	
       return $result = ['code'=>0,'msg'=>'获取成功!','data'=>$list->toArray()['data'],'count'=>$articleNum];
		
	}

	public function trashrestore(){
		$req = request();
		$id = $req->param()['id'];
		$rid = explode(',',$id);
		if (empty($rid)) {
			echo json_encode(['code'=>'10001','msg'=>'缺少请求参数']);
			die();
		}
		
		// $res = Article::destroy($rid);
		for($i = 0;$i<count($rid);$i++){
			db('article')->where('id',$rid[$i])->setField('delete_time',0);
			db('article',[],false)->alias('a')->join('category c','a.cid = c.id')->where('a.id',$rid[$i])->setInc('c.num');
		}
		parent::log($req,'在回收站恢复了'.count($rid).'篇文章');
			echo json_encode(['code'=>'0000','msg'=>'还原成功']);
	}

	public function trashdelete(){
		$req = request();
		$id = $req->param()['id'];
		$rid = explode(',',$id);
		if (empty($rid)) {
			echo json_encode(['code'=>'10001','msg'=>'缺少请求参数']);
			die();
		}
		
		$res = db('article')->delete($rid);
		
		if ($res==0) {
			echo json_encode(['code'=>'10002','msg'=>'删除0条数据']);
		}else{
			parent::log($req,'在回收站彻底删除了'.count($rid).'篇文章');
			echo json_encode(["code"=>"1","msg"=>"删除成功"]);
		}
	}
}
