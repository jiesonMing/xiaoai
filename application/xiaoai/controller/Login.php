<?php
namespace app\xiaoai\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;

/*
* jieson 2019.03.04
* 总后台登陆类
*/

class Login extends Controller
{

    public function login()
    {
        $post = inputJson();
        $account  = $post['account']?$post['account'] : '';
        $password = $post['password']?$post['password'] : '';

        if (!empty($account) && !empty($password)) {
            $password = encrypt($password);
            $res = Db::table('admin')->where('account', $account)->where('password', $password)->find();

            if ($res) {
                $token = encrypt($account.$password);
                Db::table('admin')->where('id', $res['id'])->update(['token' => $token]);
                Session::set('aid', $res['id']);

                // 返回数据
                $data['token'] = $token;
                $data['admin']['aid'] = $res['id'];
                $data['admin']['name'] = $res['name'];

                exitJson(200, $data);

            } else {
                exitJson(404, '', 'Not Found', 'data does not exist', '用户不存在');
            }
        } else {
            exitJson(404, '', 'Not Found', 'data does not exist', '账号和密码不能为空');
        }
    }
}