<?php
namespace app\xiaoai\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;

/*
* jieson 2019.03.04
* 总后台基类
*/

class Base extends Controller
{
    public function _initialize()
    {
        $this->authentication();
    }

    // 鉴权
    private function authentication()
    {
        $header = Request::instance()->header();
        if (!isset($header['x-access-token'])) {
            exitJson(401, '', 'Precondition Failed', "parameter 'X-Access-Token' empty", '鉴权失败');
        }
        $token = $header['x-access-token'];
        
        if (empty($token)) {
            exitJson(401, '', 'Precondition Failed', "parameter 'X-Access-Token' missing", '鉴权失败');
        } else {
            $res = Db::table('admin')->where('token', $token)->find();
            if (empty($res)) exitJson(401, '', 'Precondition Failed', "parameter 'X-Access-Token' invalid", '鉴权失败');

            Session::set('aid', $res['id']);

            // 判断是否登陆过期？

        }
    }

    // 总台操作日志
    protected function adminLog($aid, $module, $role=''){
        $data['aid'] = $aid;
        $data['module'] = $module;
        $data['role'] = $role;
        $data['createtime'] = date('Y-m-d H:i:s',time());
        Db::table('admin_log')->insert($data);
    }
}