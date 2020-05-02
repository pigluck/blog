<?php


namespace app\validate\index;


use think\Validate;

class ArticleDetail extends Validate
{
    public $rule = [
        'id' => 'require|number'
    ];

    public $message = [
        'id.require' => '必须传文章id'
    ];

}