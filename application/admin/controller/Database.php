<?php
/**
 * Created by Sublime Text.
 * User: LuckyPig
 */
namespace app\admin\controller;
use think\Db;
use \tp5er\Backup;
class Database extends Common{
    protected $db = '', $datadir =  './Data/';
    function _initialize(){
        parent::_initialize();
        $db=db('');
        $this->db =   Db::connect();
    }

    public function index(){
      return $this->fetch();
    }
    public function database(){
        if(request()->isPost()){
            $dbtables = $this->db->query("SHOW TABLE STATUS LIKE '".config('prefix')."%'");
            $total = 0;
            foreach ($dbtables as $k => $v) {
                $dbtables[$k]['size'] = byte_format($v['Data_length']);
                $total += $v['Data_length'] + $v['Index_length'];
            }
            return $result = ['code'=>0,'msg'=>'获取成功!','data'=>$dbtables,'total'=>format_bytes($total),'tableNum'=>count($dbtables),'rel'=>1];
        }
        return view();
    }
    //优化
    public function optimize() {
        $batchFlag = input('param.batchFlag', 0, 'intval');
        //批量删除
        if ($batchFlag) {
            $table = input('key', array());
        }else {
            $table[] = input('tableName' , '');
        }

        if (empty($table)) {
            $result['msg'] = '请选择要优化的表!';
            $result['code'] = 0;
            return $result;
        }

        $strTable = implode(',', $table);
        if (!DB::query("OPTIMIZE TABLE {$strTable} ")) {
            $strTable = '';
        }
        $result['msg'] = '优化表成功!';
        $result['code'] = 1;
        parent::log(request(),'优化了数据表'.$strTable);
        return $result;
    }
    //修复
    public function repair() {
        $batchFlag = input('param.batchFlag', 0, 'intval');
        //批量删除
        if ($batchFlag) {
            $table = input('key', array());
        }else {
            $table[] = input('tableName' , '');
        }

        if (empty($table)) {
            $result['msg'] = '请选择要修复的表!';
            $result['code'] = 0;
            return $result;
        }

        $strTable = implode(',', $table);
        if (!DB::query("REPAIR TABLE {$strTable} ")) {
            $strTable = '';
        }
        $result['msg'] = '修复表成功!';
        $result['code'] = 1;
        parent::log(request(),'修复了数据表'.$strTable);
        return $result;
    }
    //备份
    public function backup(){
      $config=array(
          'path'     => './Data/',//数据库备份路径
          'part'     => 20971520,//数据库备份卷大小
          'compress' => 0,//数据库备份文件是否启用压缩 0不压缩 1 压缩
          'level'    => 9 //数据库备份文件压缩级别 1普通 4 一般  9最高
      );
      $db= new Backup($config);
        $puttables = input('post.tables/a');
        if(empty($puttables)) {
            $dataList = $this->db->query("SHOW TABLE STATUS LIKE '".config('prefix')."%'");
            foreach ($dataList as $row){
                $table[]= $row['Name'];
            }
        }else{
            $table=input('tables/a');
        }
        $start = 0;
        $file = ['name'=>date('YmdH').'_'.rand_string(10)];
        foreach($table as $key=>$v) {
          $start= $db->setFile($file)->backup($v, $start);
        }
        parent::log(request(),'备份了数据库');
        echo json_encode(array('code'=>1,'msg'=>"成功备份数据库"));
    }
    //备份列表
    public function restore(){
        if(request()->isPost()){
            $pattern = "*.sql";
            $filelist = glob($this->datadir.$pattern);
            $fileArray = array();
            foreach ($filelist  as $i => $file) {
                //只读取文件
                if (is_file($file)) {
                    $_size = filesize($file);
                    $name = basename($file);
                    $pre = substr($name, 0, strrpos($name, '_'));
                    $number = str_replace(array($pre. '_', '.sql'), array('', ''), $name);
                    $fileArray[] = array(
                        'name' => $name,
                        'pre' => $pre,
                        'time' => date('Y-m-d h:i',filemtime($file)),
                        'sortSize' => format_bytes($_size),
                        'size' => $_size,
                        'number' => $number,
                    );
                }
            }
            if(empty($fileArray)) $fileArray = array();
            return ['code'=>0,'msg'=>'获取成功!','data'=>$fileArray,'rel'=>1];
        }
        return view();
    }
    //执行还原数据库操作
    public function restoreData() {
      $filename = input('sqlfilepre');
      $db= new Backup();
      // $file = "2019022412_TfCheYvJMd.sql";
      $db->setFile($filename)->import(0);
        $result['msg'] = '数据库还原成功!';
        $result['code'] = 1;
        parent::log(request(),'还原了数据库'.$filename);
        echo json_encode($result);
    }

    public function excuteQuery($sql=''){
        if(empty($sql)) {$this->error('空表');}
        $queryType = 'INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|LOAD DATA|SELECT .* INTO|COPY|ALTER|GRANT|TRUNCATE|REVOKE|LOCK|UNLOCK';
        if (preg_match('/^\s*"?(' . $queryType . ')\s+/i', $sql)) {
            $data['result'] = $this->db->execute($sql);
            $data['type'] = 'execute';
        }else {
            $data['result'] = $this->db->query($sql);
            $data['type'] = 'query';
        }
        $data['dberror'] = $this->db->getError();
        return $data;
    }
    function  sql_split($sql,$tablepre) {
        if($tablepre != "chrr_") $sql = str_replace("chrr_", $tablepre, $sql);
        //$sql = preg_replace("/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/", "ENGINE=\\1 DEFAULT CHARSET=utf8",$sql);

        if($r_tablepre != $s_tablepre) $sql = str_replace($s_tablepre, $r_tablepre, $sql);
        $sql = str_replace("\r", "\n", $sql);
        $ret = array();
        $num = 0;
        $queriesarray = explode(";\n", trim($sql));
        unset($sql);
        foreach($queriesarray as $query)
        {
            $ret[$num] = '';
            $queries = explode("\n", trim($query));
            $queries = array_filter($queries);
            foreach($queries as $query)
            {
                $str1 = substr($query, 0, 1);
                if($str1 != '#' && $str1 != '-') $ret[$num] .= $query;
            }
            $num++;
        }
        return $ret;
    }
    //下载
    public function downFile() {
        $file = $this->request->param('file');
        $type = $this->request->param('type');
        if (empty($file) || empty($type) || !in_array($type, array("zip", "sql"))) {
            $this->error("下载地址不存在");
        }
        $path = array("zip" => $this->datadir."zipdata/", "sql" => $this->datadir);
        $filePath = $path[$type] . $file;
        if (!file_exists($filePath)) {
            $this->error("该文件不存在，可能是被删除");
        }
        $filename = basename($filePath);
        header("Content-type: application/octet-stream");
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header("Content-Length: " . filesize($filePath));
        readfile($filePath);
        parent::log(request(),'下载了数据库备份文件'.$filename);
    }
    //删除sql文件
    public function delSqlFiles() {
        $batchFlag = input('param.batchFlag', 0, 'intval');
        //批量删除
        if ($batchFlag) {
            $files = input('key', array());
        }else {
            $files[] = input('sqlfilename' , '');
        }
        if (empty($files)) {
            $result['msg'] = '请选择要删除的sql文件!';
            $result['code'] = 0;
            return $result;
        }

        foreach ($files as $file) {
            $a = unlink($this->datadir.'/' . $file);
            parent::log(request(),'删除了数据库备份文件'.$file);
        }
        if($a){
            $result['msg'] = '删除成功!';
            $result['url'] = url('restore');
            $result['code'] = 1;

            return $result;
        }else{
            $result['msg'] = '删除失败!';
            $result['code'] = 0;
            return $result;
        }
    }
}