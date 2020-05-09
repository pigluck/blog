<?php
namespace app\controller;

use app\BaseController;
use think\facade\Db;
use think\facade\View;

class Index extends BaseController
{
    public function index()
    {
        $data = Db::name('article')->limit(10)->select()->toArray();
        View::assign([
            'lb' => array_slice($data,0,3),
            'topic' => array_slice($data,0,2),
            'news' => array_slice($data,0,6),
            'blogs' => $data,
            'dj' => array_slice($data,0,8),
            'tj' => array_slice($data,0,8),
            'category' => [
                [
                    'name' => '学无止境',
                    'count' => 89,
                    'path' => 'df'
                ],
                [
                    'name' => '日记',
                    'count' => 89,
                    'path' => 'df'
                ],
                [
                    'name' => '慢生活',
                    'count' => 89,
                    'path' => 'df'
                ],
            ]
        ]);
        return view('index');
    }
}
