<?php
namespace app\admin\controller;
class Auth extends Common{
    public function _initialize()
    {
        parent::_initialize();
    }

    public function index(){
        return $this->fetch();
    }

    public function adminlist(){
        $list = db('admin')->order('admin_id desc')->where('admin_id','neq',1)->paginate(10);
        $vipNum = db('admin')->count();

        return $result = ['code'=>0,'msg'=>'获取成功!','data'=>$list->toArray()['data'],'count'=>$vipNum-1];
    }

    public function add(){
        $children = db('auth_rule')->where('pid','neq',0)->field('id,title,pid')->select();
        $menus = db('auth_rule')->where('pid',0)->field('id,title,pid')->select();
        foreach ($menus as $k=>$v){
            foreach ($children as $kk=>$vv){
            if($v['id']==$vv['pid']){
            $menus[$k]['children'][] = $vv;
            }
        }
        }
        $this->assign('auth',$menus);
        return $this->fetch();
        
    }

    public function save(){
        $req = request()->param();
        $flag=true;
        if (empty($req['id'])) {
            unset($req['id']);
            $flag=false;
        }else{
            $req['admin_id'] = $req['id'];
            unset($req['id']);
        }
        
        foreach ($req['auth'] as $v){
            $data[] = $v;
        }
        $list = implode(',',$data);
        unset($req['auth']);
        $req['pwd'] = md5($req['pwd']);
        $req['rules'] = $list;
        // dump($req);die();
        if ($flag) {
            db('admin')->where('admin_id',$req['admin_id'])->update($req);
            parent::log(request(),'修改了管理员'.$req['username'].'信息');
            echo json_encode(["code"=>"0000","msg"=>"修改成功"]);
            die();
        }else{
            
            $res = db('admin')->insert($req);
            if ($res){
                parent::log(request(),'添加了管理员'.$req['username']);
                echo json_encode(["code"=>"0000","msg"=>"添加成功"]);
            }else{
                echo json_encode(["code"=>"0010","msg"=>"添加失败"]);
            }
        }
    }

    public function delete(){
        $req = request();
        $id = $req->param()['id'];
        if (empty($id)) {
            echo json_encode(['code'=>'10001','msg'=>'缺少请求参数']);
            die();
        }

        $res = db('admin')->delete($id);

        if ($res==0) {
            echo json_encode(['code'=>'10002','msg'=>'删除0条数据']);
        }else{
            parent::log($req,'删除一个管理员账号');
            echo json_encode(["code"=>"1","msg"=>"删除成功"]);
        }
    }

    public function edit(){
        $req = request()->param();
        $id = $req['id'];
        $info = db('admin')->where('admin_id',$id)->field('admin_id,username,tel,email,rules')->find();
        if (empty($info)) {
            return false;
        }

        echo json_encode($info);
    }

}