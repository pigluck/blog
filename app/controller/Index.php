<?php
namespace app\controller;

use app\BaseController;
use think\facade\View;
use app\validate\index\User;

class Index extends BaseController
{
    public function index()
    {
        try {
            Validate(User::class)->check(['ll' => 11]);
        }catch (\Exception $e){
            var_dump($e->getMessage());
        }
        return View::fetch('index');
    }

    public function hello()
    {
        var_dump($_POST);
    }
}
