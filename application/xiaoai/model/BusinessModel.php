<?php
/**
 * jieson 2019.03.04
 * 商家表
 */

namespace app\xiaoai\model;
use think\Model;

class BusinessModel extends Model
{
    protected $table = 'business';
    protected $field = true;

    // 套餐
    public function package()
    {
        return $this->belongsTo('BusinessPackage', 'packageid', 'id');
    }

    // 分类
    public function cate()
    {
        return $this->belongsTo('BusinessCate', 'cateid', 'id');
    }

    public function getBusinessById($id)
    {
        $business = self::with('package,cate')->find($id); // with 接收一个数组
        return $business;
    }
}