<?php

namespace app\common\exception;

class Exception extends BaseException {
    public $code = 404;
    public $msg = '用户不存在';
    public $errorCode = 60000;
}