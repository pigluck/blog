<?php
/**
 * Created by PhpStorm.
 * User: huson
 * Date: 2019/3/2
 * Time: 11:16
 */

namespace app\index\controller;
use app\index\model\User;
use think\Loader;


class Comment{
    public function addcomment(){
        $req = request()->param();
        $req['create_time'] = time();
        db('comment')->insert($req);
        $this->user();
        $this->log(request(),1);
        return json_encode($req);
    }

    public function reply(){
        $req = request()->param();
        $rname = db('user')->where('uid',$req['rid'])->value('username');
        $req['rname'] = $rname;
        $req['create_time'] = time();
        unset($req['rid']);
        db('comment')->insert($req);
        $this->user();
        $this->log(request(),2);
        return json_encode($req);
    }

    private function user(){
        if (!empty(cookie('uid'))) {
            $user = User::get(function($query){
            $query->where('uid',cookie('uid'));
            });
                
            $user->credit = $user->credit+5;
            $user->exp = $user->exp+20;
            $user->save();
            }
        }

    private function log($Request,$operation){
 $res = getCity($Request->ip());
        if ($res->status == 1) {
            Loader::import('ip.IpLocation', EXTEND_PATH,'.class.php');
            $Ip = new \IpLocation('UTFWry.dat');
            $city = $Ip->getlocation($Request->ip());
        }else{
            $city = $res->content->address;
        } 
            $log = [
                'uid' => cookie('uid'),
                'username' => cookie('username'),
                'ip' => $Request->ip(),
                'city' => $city,
                'operation' => $operation,
                'create_time' => time()
            ];
            db('log')->insert($log);
        
    }

}