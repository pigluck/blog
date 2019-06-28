<?php
/**
 * Created by Sublime Text.
 * User: LuckyPig
 */
namespace app\index\controller;
use think\Db;
use think\Request;


class EmptyController extends Common{
    protected  $dao,$fields;
    protected $language;
    public function _initialize()
    {
        parent::_initialize();
        $this->language = cookie('lang')?:'zh-cn';
    }
    public function index(){
        // dump(MODULE_NAME);
        $template = db('category')->where('catdir',MODULE_NAME)->value('template');
        if (!$template) {
          $template = 'list';
        }else{
          $template = explode('.',$template)[0];
        }
        
        if (MODULE_NAME == "time") {
            $artinfo = db('article')->where('delete_time',0)->order('update_time desc')->where('lang',$this->language)->paginate(20);
        }else{
            $catinfo = db('category')->where('catdir',MODULE_NAME)->field('id,catname,catdir,keywords,description,arrchildid')->find();
            if (!$catinfo) {
                return $this->fetch('exception_html/404');
            }
            $this->assign('catinfo',$catinfo);
            $artinfo = db('article')->where('cid','in',$catinfo['arrchildid'])->where('delete_time',0)->where('lang',$this->language)->order('update_time desc')->paginate(13);
             
        }
        $page = $artinfo->render();
        $this->assign('artinfo',$artinfo);
        $this->assign('page',$page);
        return $this->fetch('index/'.$template);
    }
}