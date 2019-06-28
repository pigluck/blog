<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],
    'adminpic'=>'admin/index/upload',
    'login'=>'admin/adminlogin/index',
    'about'=>'index/about/index',
    'xwzj'=>'index/xwzj/index',
    'php'=>'index/php/index',
    'html'=>'index/html/index',
    'life'=>'index/life/index',
    'joke'=>'index/joke/index',
    'linux'=>'index/linux/index',
    'jz'=>'index/jz/index',
    'dede'=>'index/dede/index',
    'time'=>'index/time/index',
    'gbook'=>'index/gbook/index',
    'article/:id'=>'index/index/article',
    'zan'=>'index/index/zan',
    'tag/:name'=>['index/index/tag',['name'=>'.+']],
    'search'=>'index/index/search',
    'reg'=>'index/reg/index',
    'userLogin'=>'index/reg/userLogin',
    'usercheck'=>'index/reg/user',
    'phonesms'=>'index/reg/sendsms',
    'checkCode'=>'index/reg/checkCode',
    'logincheckuser'=>'index/reg/logincheckuser',
    'checklogin'=>'index/reg/checklogin',
    'logout'=>'index/reg/logout',
	'login/findpwd'=>'index/findpwd/index',
    'findstept'=>'index/findpwd/findstept',
    'findcheckCode'=>'index/findpwd/findcheckCode',
    'sendsms'=>'index/findpwd/sendsms',
	'comment/addcomment'=>'index/comment/addcomment',
    'comment/reply'=>'index/comment/reply',
    'lang'=>'index/index/lang',
];
