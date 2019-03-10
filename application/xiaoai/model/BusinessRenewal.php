<?php
/**
 * jieson 2019.03.04
 * 商家续费升级表
 */

namespace app\xiaoai\model;
use think\Model;

class BusinessRenewal extends Model
{
    protected $field = true;

    public function business()
    {
        return $this->belongsTo('BusinessModel', 'bid', 'id');
    }

    public function newPackage()
    {
        return $this->belongsTo('BusinessPackage', 'packageid', 'id');
    }

    public function oldPackage()
    {
        return $this->belongsTo('BusinessPackage', 'oldpackageid', 'id');
    }

    public function getBusinessRenewalById($id)
    {
        $business_renewal = self::with('business,newPackage,oldPackage')->find($id); // with 接收一个数组
        $business_renewal = $business_renewal->visible([
            'id','status','createtime','payvoucher_url',
            'business.businessname',
            'business.phone',
            'business.provinceid',
            'business.cityid',
            'business.areaid',
            'business.provinces',
            'business.address',
            'business.buytime',
            'business.expiretime',
            'old_package.packagename',
            'old_package.packageprice',
            'old_package.desc',
            'new_package.packagename',
            'new_package.packageprice',
            'new_package.desc',
            ]);
        return $business_renewal;
    }
}