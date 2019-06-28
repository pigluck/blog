<?php 
/**
 * Created by Sublime Text.
 * User: LuckyPig
 */
namespace app\admin\controller;

class Category extends Common{
    protected $cid;
	public function _initialize(){
        parent::_initialize();
        $index=db('category')->select();
        $res=$this->sort($index);
             // dump($res);die();
        $this->assign('cattree',$res);
        $path = APP_PATH.'index/view/index';
        $files = dir_list($path,'html');
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
    }

    public function index(){
    	$catlist = db('category')->field('id,catname,catdir,num,open')->select();
    	
    	$this->assign('catlist',$catlist);
    	
    	return $this->fetch();
    }

    public function sort($data,$pid=0,$level=0){
        static $arr = array();
        foreach($data as $k=>$v){
            if($v['parentid'] == $pid){
                $v['level'] = $level;
                $arr[] = $v;
                $this->sort($data,$v['id'],$level+1);
            }
        }
        return $arr;
    }

    public function add(){
        $request = request();
    	$req = request()->param();
    	// dump($req);die();
    	if (empty($req['id'])) {
    		unset($req['id']);
    		$id = db('category')->insertGetId($req);
            if ($id) {
                parent::log($request,'新添加了一个栏目');
                $this->cid = $id;
                $res = $this->updatechild($id);
                }
    	}else{ 
            $parentid = db('category')->where('id',$req['id'])->value('parentid');
            
            if ($parentid == $req['parentid']) {
                $res = db('category')->where("id",$req['id'])->update($req);
            }else{
                $this->cid = $req['id'];
                $del = $this->delchild($parentid);
                $update = db('category')->where("id",$req['id'])->update($req);
                $child = $this->updatechild($req['parentid']);
                if ($del && $update && $child) {
                    $res = true;
                }else{
                    $res = false;
                }
            }
            parent::log($request,'修改了id为'.$req['id'].'的栏目');
    		
    	}
    	if ($res) {

    		echo json_encode(['code'=>"0000","msg"=>"添加成功"]);
    	}else{
    		echo json_encode(['code'=>"1001","msg"=>"添加失败"]);
    	}
    }

    public function edit(){
    	$req = request()->param();
    	$id = $req['id'];
    	if (empty($id)) {
    		echo json_encode(["code"=>"1001","msg"=>"缺少请求参数"]);
    	}else{
    		$res = db('category')->where("id",$id)->field('id,catname,catdir,parentid,keywords,description,open,lx,template')->find();
    		if ($res) {
    			$res['code'] = "0000";
    			echo json_encode($res);
    		}else{
    			echo json_encode(["code"=>"1002","msg"=>"查找失败"]);
    		}
    	}
    }

    public function delete(){
        $request = request();
    	$req = request()->param();
    	$id = $req['id'];
    	if (empty($id)) {
    		echo json_encode(["code"=>"1001","msg"=>"缺少请求参数"]);
    	}else{
            $parentid = db('category')->where('id',$req['id'])->value('parentid');
            if ($parentid != 0) {
                $this->cid = $req['id'];
                $del = $this->delchild($parentid);
            }
    		$cres = db('category')->where("id",$id)->delete();
    		$ares = db('article')->where('cid',$id)->delete();
    		if(!$cres && !$ares){
    			echo json_encode(["code"=>"1002","msg"=>"删除出错"]);
    		}else{
                parent::log($request,'删除了id为'.$id.'栏目,包括子栏目');
    			echo json_encode(["code"=>"0000","msg"=>"删除成功"]);
    		}
    	}
    }

    public function updatechild($id){
        $cat = db('category')->where('id',$id)->field('arrchildid,parentid')->find();
        if (empty($cat['arrchildid'])) {
            $childid = $this->cid;
        }else{
            $childid = $cat['arrchildid'].','.$this->cid;
        }
        db('category')->where('id',$id)->update(['arrchildid'=>$childid]);
        $parentid=$cat['parentid'];
        if ($parentid!=0) {
            return $this->updatechild($parentid);
        }else{
            return true;
        }
    }

    public function delchild($parentid){
        $cat = db('category')->where('id',$parentid)->field('parentid,arrchildid')->find();
        $childid = explode(',',$cat['arrchildid']);
        foreach ($childid as $key => $value) {
            if ($value==$this->cid) {
                unset($childid[$key]);
            }
        }
        $allchildid = implode(',',$childid);
        db('category')->where('id',$parentid)->update(['arrchildid'=>$allchildid]);
        if ($cat['parentid']==0) {
            return true;
        }else{
            return $this->delchild($cat['parentid']);
        }
    }

    public function open(){
        $request = request();
        $req = request()->post();

        $res = db('category')->update($req);
        if ($res) {
            parent::log($request,'改变了id为'.$req['id'].'的栏目的开启状态');
            echo json_encode(['code'=>1,'msg'=>'修改成功']);
        }else{
            echo json_encode(['code'=>0,'msg'=>'修改失败']);
        }
    }

    public function updatecat(){
        return $this->fetch();
    }
}