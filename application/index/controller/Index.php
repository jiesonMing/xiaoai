<?php
namespace app\index\controller;
use think\Db;

class Index
{
    public function index()
    {
        $res = db('users')->where('uid',2)->find();
        // $res = Db::table('users')->where('uid',1)->find();
        if ($res) {
            successMsg($res);
        } else {
            errorMsg('Not Found', 'data does not exist', '数据不存在');
        }
        
    }
}
