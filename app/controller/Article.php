<?php


namespace app\controller;


use app\BaseController;
use app\service\IndexService;
use app\validate\index\ArticleDetail;

class Article extends BaseController
{
    public function articleDetail()
    {
        $validate = new  ArticleDetail();
        if ($validate->check($request = $this->request->param())){
            $result = (new IndexService)->index();
            return view('articleDetail',$result);
        }else{
            return abort(404,$validate->getError());
        }
    }
}