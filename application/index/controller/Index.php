<?php
/**
 * Created by Sublime Text.
 * User: LuckyPig
 */
namespace app\index\controller;
use app\index\model\Article;

class Index extends Common{
	public function _initialize(){
        parent::_initialize();
  }
  
  public function index(){
     return $this->fetch();
  }

  public function article(){
  	$id = request()->param()['id'];
  	if (empty($id)) {
  		 return $this->fetch('exception_html/404');
  	}
  	$info = db('article')->where('id',$id)->where('delete_time',0)->find();
    if (empty($info)) {
      return $this->fetch('exception_html/404');
  		 
  	}
  	// db('article')->where('id',$id)->setInc('hits');
    // Article::where('id',$id)->setInc('hits');
    $article = Article::get($id);
    $article->hits = $article->hits+1;
    $article->save();
  	$this->assign('info',$info);
    $this->assign('aid',$id);
  	$tagarr = explode(",",$info['tag']);
  	$this->assign('tagarr',$tagarr);
    $lang = cookie('lang')?:'zh-cn';
  	$more = db('article')->where('cid',$info['cid'])->where('id','neq',$info['id'])->where('delete_time',0)->where('lang',$lang)->select();
  	$this->assign('moreart',$more);
  	$preart = db('article')->where('cid',$info['cid'])->where('id','lt',$info['id'])->where('lang',$lang)->where('delete_time',0)->order('id desc')->limit(1)->find();
  	if (!$preart) {
  		$catdir = db('category')->where('id',$info['cid'])->value('catdir');
  		$pre = "<a href='/".$catdir."'>返回列表</a>";
  	}else{
  		$pre = "<a href='/article/".$preart['id']."'>".$preart['title']."</a>";
  	}
  	$nextart = db('article')->where('cid',$info['cid'])->where('id','gt',$info['id'])->where('lang',$lang)->where('delete_time',0)->order('id asc')->limit(1)->find();
  	if (!$nextart) {
  		$catdir = db('category')->where('id',$info['cid'])->value('catdir');
  		$next = "<a href='/".$catdir."'>返回列表</a>";
  	}else{
  		$next = "<a href='/article/".$nextart['id']."'>".$nextart['title']."</a>";
  	}
  	$this->assign('pre',$pre);
  	$this->assign('next',$next);
    $template = db('article')->where('id',$id)->value('template');
    if (!$template) {
      $template = 'article';
    }else{
      $template = explode('.',$template)[0];
    }
    $comment = db('comment')->alias('c')->join('user u',"c.uid = u.uid")->where('c.aid',$id)->field('u.logo,u.username,c.id,u.uid,c.content,c.create_time,c.parentid,c.rname')->select();
    $commentinfo =  $this->csort($comment);
    $this->assign('commentinfo',$commentinfo);
    if (!empty(cookie('uid')) && !empty('username')) {
      parent::log(request(),cookie('uid'),cookie('username'),'访问ID为'.$id.'的文章');
    }else{
      parent::log(request(),cookie('USERID'),'游客','访问ID为'.$id.'的文章');
    }
$this->fwl('zl');
  	return $this->fetch($template);
  }

  public function zan(){
  	$id = request()->param()['id'];
  	db('article')->where('id',$id)->setInc('zan');
  }

  public function tag($name){
    $lang = cookie('lang')?:'zh-cn';
  	$name = base64_decode($name);
  	if (empty($name)) {
  		 return $this->fetch('exception_html/404');
  	}
  	$artinfo = db('article')->where('tag','like',"%$name%")->where('delete_time',0)->where('lang',$lang)->order('update_time desc')->paginate(13);
  	$page = $artinfo->render();
  	$this->assign('artinfo',$artinfo);
  	$this->assign('page',$page);
  	return $this->fetch('list');
  }

  public function search(){
  	$name=request()->param()['keyboard'];
  	if (empty($name)) {
  		return $this->fetch('exception_html/404');
  	}
  	$artinfo = db('article')->where('title|content','like',"%$name%")->order('update_time desc')->paginate(13);
  	$page = $artinfo->render();
  	$this->assign('artinfo',$artinfo);
  	$this->assign('page',$page);
  	return $this->fetch('index/list');
  }
  
  private function csort($data){
        static $arr = array();
        for ($i = 0;$i<count($data);$i++){
            if ($data[$i]['parentid'] == 0){
                $data[$i]['sub'] = [];
                foreach($data as $k=>$v){
                    if($v['parentid'] == $data[$i]['id']){
                        array_push($data[$i]['sub'],$v);
                    }
                }
                array_push($arr,$data[$i]);
            }

        }

        return $arr;
    }
  
  public function lang(){
        $lang = request()->post('str');
        cookie('think_var',$lang);
        cookie('lang',$lang);
        return true;
    }
}
