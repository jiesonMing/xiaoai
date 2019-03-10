<?php
/**
 * jieson 2019.03.07
 * 用户表
 */

namespace app\xiaoai\model;
use think\Model;

class UsersModel extends Model
{
    protected $table = 'users';
    protected $field = true;

    public function business()
    {
        return $this->belongsTo('BusinessModel','bid','id');
    }

    public function getUsersById($id)
    {
        $users = $this->find(['uid' => $id])->visible([
            'username','sex','img_url','phone','email','createtime','useamount'
        ]);
        if($users->isbusiness == 1 && $users->bid != 0) {
            $users = self::with('business')->find(['uid' => $id])->visible([
                'username','sex','img_url','phone','email','createtime','useamount',
                'business.businessname',
                'business.createtime'
            ]);
        }
        return $users;
    }

}
    