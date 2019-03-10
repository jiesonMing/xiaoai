<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

// 成功返回的对象
// function successMsg($arr)
// {
//     exit(json_encode($arr));
// }

/* 返回的对象
* @int $statusCode 状态吗 200,40x-50x
* @string $type 错误状态吗类型
* @string $reason 英文的错误提示
* @string $message 错误提示
* @array $data 返回的数据
*/
function exitJson($statusCode, $data = '', $type = '', $reason = '', $message = '')
{
    $back = [];
    if ($statusCode != 200) {
        $back['error']['type'] = $type;
        $back['error']['reason'] = $reason;
        $back['error']['message'] = $message;
    } else {
        $back = $data;
    }

    http_response_code($statusCode);
    header("status:".$statusCode);
    header("content-type:application/json;charset=utf-8");
    header("x-powered-by:");
    header("expires:");
    header("cache-control:");
    header("pragma:");
    header("content-length:");
    header("connection:");
    
    exit(json_encode($back));
}

/*
* 接收json
*/
function inputJson()
{
    return json_decode(file_get_contents('php://input'), true);
}

/* 公共密码加密
*/
function encrypt($str)
{
    $passKey = 'xiaoai';
    return md5(md5($str.$passKey));
}

