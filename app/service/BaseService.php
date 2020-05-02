<?php


namespace app\service;


use think\facade\View;

class BaseService
{
    public function __construct()
    {
        View::assign('name','4654');
    }
}