<?php
namespace app\controller;

use app\BaseController;
use Predis\Client;
use think\cache\driver\Redis;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Db;
use think\facade\View;
use util\RedisUtil;

class Index extends BaseController
{
    public function index()
    {
        $redis = RedisUtil::getInstance()->redis;
        $aa = $redis->del('kod');
        var_dump($aa);die;
        return View::fetch('index');
    }

    public function hello()
    {
        var_dump($_POST);
    }
}
