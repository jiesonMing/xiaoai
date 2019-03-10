<?php
namespace app\xiaoai\controller;
use think\Controller;
use think\Db;
use think\Validate;

/* 小艾商城短信发送以及验证类
*/

class Sms extends Controller
{
    /* 发送
    */
    public function send_sms_code()
    {
        $data['phone'] = Request::instance()->param('phone');

        $validate = new Validate([
            ['phone','max:11|/^1[3-8]{1}[0-9]{9}$/', '请输入正确的手机号']
        ]);

        if (!$validate->check($data)) {
            exitJson(400, '', "Data bad",'data not empty', $validate->getError());
        }

        $code = rand(100000,99999);
        
        // 发送验证码的服务商


        $res = true;
        // 把验证码保存到发送记录表
        if ($res) {
            $time = date('Y-m-d H:i:s', time());
            Db::table('smscode')->insert([
                'phone' => $data['phone'],
                'code'  => $code,
                'createtime' => $time
            ]);
            //保存code到cookie
            cookie('code',$code);
        }
    }

    /* 验证
    */
    public function validate_code($code, $phone)
    {
        if (empty($code) || empty($phone)) {
            exitJson(400, '', "Data bad",'data not empty', '手机号和验证码不能为空');
        }

        // method -
        $oldCode = cookie('code')?cookie('code'):'';
        if ($oldCode != $code) {
            exitJson(400, '', "Data bad",'data not right', '验证码不正确');
        }

        // methode 二
        $res = Db::table('smscode')
        ->where(['phone' => $phone, 'code' => $code])
        ->order('createtime', 'desc')
        ->find();

        if(!$res) {
            exitJson(400, '', "Data bad",'data not right', '验证码不正确');
        }
        return true;
    }
}