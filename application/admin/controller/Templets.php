<?php
/**
 * Created by Sublime Text.
 * User: LuckyPig
 */
namespace app\admin\controller;

class Templets extends Common{
	public function _initialize(){
        parent::_initialize();
    }

    public function index(){
    	$req = request()->param();
    	$type = $req['type'];
    	$path = $this->switchpath($type);
    	$files = dir_list($path,$type);
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
        $this->assign('type',$type);
        return $this->fetch();
    }

    public function edit(){
    	$req = request()->param();
    	$name = $req['name'];
    	$type = $req['type'];
    	$path = $this->switchpath($type);
    	$file = $path.'/'.$name.'.'.$type;
        if(file_exists($file)){
            $file=iconv('gb2312','utf-8',$file);
            $content = htmlspecialchars(file_get_contents($file));
            $this->assign ( 'filename',$name.'.'.$type);
            $this->assign ( 'title','修改模版内容' );
            $this->assign ( 'file',$file );
            $this->assign ( 'content',$content );
        }else{
            $this->error('文件不存在！');
        }
        return $this->fetch();
        // dump($content);
    }

    public function update(){
    	$req = request()->post();
    	$content = $req['content'];
    	$filename=$req['filename']; 
    	$arr = explode('.',$filename);
    	$type = $arr[count($arr)-1];
    	$path = $this->switchpath($type);
    	$file = $path.'/'.$filename;
        if(file_exists($file)){
            file_put_contents($file,htmlspecialchars_decode($content));
            parent::log(request(),'修改了模板文件'.$filename);
            echo json_encode(['code'=>'0000','msg'=>'修改成功']);
        }else{
            echo json_encode(['code'=>'0001','msg'=>'文件不存在']);
        }
    	
    }

    public function add(){
        $this->assign ( 'title','添加模版' );
        return $this->fetch();
    }

    public function insert(){
        $req = request()->post();
    	$content = $req['content'];
    	$filename=$req['filename']; 
        $arr = explode('.',$filename);
    	$type = $arr[count($arr)-1];
        $path = $this->switchpath($type);
        $file = $path.'/'.$filename;
        if(file_exists($file)){
            echo json_encode(['code'=>'0001','msg'=>'文件已经存在']);die();
        }
        file_put_contents($file,htmlspecialchars_decode(stripslashes($content)));
        parent::log(request(),'添加了模板文件'.$filename);
        echo json_encode(['code'=>'0000','msg'=>'添加成功']);die();
    }

    private function switchpath($type){
    	switch ($type) {
    		case 'html':
    			$path = APP_PATH.'index/view/index';
    			break;
    		case 'js':
    		    $path = ROOT_PATH.'public/static/index/js';
    			break;
    		case 'css':
    			$path = ROOT_PATH.'public/static/index/css';
    			break;
    		default:
    			$path = '';
    			break;
    	}
    	return $path;
    }
}