<?php
namespace app\xiaoai\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\Validate;
use app\xiaoai\model\BusinessModel;
use app\xiaoai\model\BusinessCate;
use app\xiaoai\model\BusinessPackage;
use app\xiaoai\model\BusinessRenewal;

use app\common\exception\Exception;


/*
* jieson 2019.03.04
* 后台商家管理接口
*/

class Business extends Base
{
    public $aid = 0; // 管理员id
    public $role = ''; // 管理员角色
    
    public function _initialize() {      
        parent::_initialize();
        $this->aid = Session::get('aid');
        $this->role = '管理员'; // 默认
    }

    /* 查询商家
    * @string startTime、endTime
    */
    public function business_list()
    {
        if (!Request::instance()->isGet()) exitJson(400, '', 'Bad Request', ' Request type error', '请求类型错误');
        
        $get = input('get.');
        $searchWord = trim(input('get.search'));
        $isHide = trim(input('get.status'));// 0全部 ,1正常 ,2已屏蔽

        $pageIndex = isset($get['pageIndex']) && $get['pageIndex'] !=''?$get['pageIndex']:1; // 页数
        $pageSize  = isset($get['pageSize']) && $get['pageSize'] !=''?$get['pageSize']:10; // 条数
        $limitStart = ($pageIndex-1)*$pageSize;

        $where = '1 ';
        $where.= isset($get['startTime']) && $get['startTime'] !=''? " and createtime > ".$get['startTime']:'';
        $where.= isset($get['endTime']) && $get['endTime'] !=''? " and createtime < ".$get['endTime']:'';
        $where.= $searchWord!=''? " and concat(businessname,phone,registname) like '%".$searchWord."%'" : '';
        if ($isHide ==0 || $isHide == '') {
            $where .= " and ishide in(0,1)";
        } else if ($isHide == 1) {
            $where .= " and ishide = 0";
        } else if ($isHide == 2) {
            $where .= " and ishide = 0";
        } else {
            $where .= " and ishide = -1";
        }

        if (isset($get['id']) && !empty($get['id'])) {
            $business = BusinessModel::get($get['id']);
            $res = $business->getBusinessById($get['id']);
        } else {
            $business = new BusinessModel();
            $res = $business->where($where)
                ->limit($limitStart, $pageSize)
                ->order('id', 'desc')
                ->select();
        }
        
        if ($res) {
            exitJson(200,$res);
        } else {
            exitJson(404, '', 'Not Found', 'data does not exist', '数据不存在');
        }
        
    }

    /* 会员入驻审核
    * @int id
    * @int status 1通过，2拒绝，拒绝会有理由
    * @string remark(请求为reason)
    */
    public function business_check()
    {
        if (Request::instance()->isPut()) {
            $put = input('put.');
            $data['id'] = $put['id'];
            $data['status'] = $put['status'];
            $data['remark'] = isset($put['reason'])?$put['reason']:'';
            $data['updatetime'] = date('Y-m-d H:i:s', time());
            $pass = $put['status'] == 1?'通过':'拒绝';

            Db::startTrans();
            try {
                $business = new BusinessModel();
                $business->save($data, ['id' => $data['id']]);
                
                $this->adminLog($this->aid, "商家管理>商家审核>".$pass, $this->role);
                $backData = BusinessModel::get($business->id);
                Db::commit();
                exitJson(200, $backData);
            } catch (Exception $e) {
                exitJson(400, '', 'Bad Request', ' Request type error', $e->getMessage());
            }
            
        } else {
            exitJson(400, '', 'Bad Request', ' Request type error', '请求类型错误');
        }
    }

    /* 商家续费
    * int: pageIndex pageSize status
    * string: search startTime endTime
    */
    public function business_renewal_list()
    {
        if (!Request::instance()->isGet()) exitJson(400, '', 'Bad Request', ' Request type error', '请求类型错误');
        
        $get = input('get.');
        $searchWord = trim(input('get.search'));
        $status = trim(input('get.status'));// 0全部 ,1待审核->0 ,2通过->1,3拒绝->2

        $pageIndex = isset($get['pageIndex']) && $get['pageIndex'] !=''?$get['pageIndex']:1; // 页数
        $pageSize  = isset($get['pageSize']) && $get['pageSize'] !=''?$get['pageSize']:10; // 条数
        $limitStart = ($pageIndex-1)*$pageSize;

        $where = '1 ';
        $where.= isset($get['startTime']) && $get['startTime'] !=''? " and br.createtime > ".$get['startTime']:'';
        $where.= isset($get['endTime']) && $get['endTime'] !=''? " and br.createtime < ".$get['endTime']:'';
        $where.= $searchWord!=''? " and concat(b.businessname,b.phone,b.registname) like '%".$searchWord."%'" : '';
        if ($status ==0 || $status == '') {
            $where .= " and br.status in(0,1,2)";
        } else if ($status == 1) {
            $where .= " and br.status = 0";
        } else if ($status == 2) {
            $where .= " and br.status = 1";
        } else if ($status == 3) {
            $where .= " and br.status = 2";
        } else {
            $where .= " and br.status = -1";
        }

        if (isset($get['id']) && !empty($get['id'])) {
            $BusinessRenewal = BusinessRenewal::get($get['id']);
            $res = $BusinessRenewal->getBusinessRenewalById($get['id']);
        } else {
            $BusinessRenewal = new BusinessRenewal();
            $res = $BusinessRenewal->alias('br')
                ->join('business b', 'b.id=br.bid', 'left')
                ->join('business_package bp', 'bp.id=br.packageid', 'left')
                ->where($where)
                ->field("br.id,br.createtime,br.status,b.logo_url,b.businessname,b.registname,b.phone,bp.packagename")
                ->limit($limitStart, $pageSize)
                ->order('br.id', 'desc')
                ->select();
        }
        
        if ($res) {
            exitJson(200,$res);
        } else {
            exitJson(404, '', 'Not Found', 'data does not exist', '数据不存在');
        }
    }

    /* 商家续费审核
    * @int id
    * @int status 1通过，2拒绝，拒绝会有理由
    * @string remark(请求为reason)
    */
    public function business_renewal_check()
    {
        $time = date('Y-m-d H:i:s', time());
        if (Request::instance()->isPut()) {
            $put = input('put.');
            $data['id'] = $put['id'];
            $data['status'] = $put['status'];
            $data['remark'] = isset($put['reason'])?$put['reason']:'';
            $data['updatetime'] = $time;
            $pass = $put['status'] == 1?'通过':'拒绝';

            Db::startTrans();
            try {
                $BusinessRenewal = new BusinessRenewal();
                $res = $BusinessRenewal->save($data, ['id' => $data['id']]);
                
                // 审核通过的，更新商家信息
                if ($res) {
                    $backData = BusinessRenewal::get($BusinessRenewal->id);

                    if ($data['status'] == 1) {
                        $updateData['buytime'] = $backData->createtime;
                        $updateData['expiretime'] = date('Y-m-d', strtotime($backData->createtime."+1year"));
                        $updateData['packageid'] = $backData->packageid;
                        $updateData['updatetime'] = $time;

                        $business = new BusinessModel();
                        $business->save($updateData, ['id' => $backData->bid]);
                    }

                    $this->adminLog($this->aid, "商家管理>续费审核>".$pass, $this->role);
                    Db::commit();
                    
                    exitJson(200, $backData);
                }
                
            } catch (Exception $e) {
                exitJson(400, '', 'Bad Request', ' Request type error', $e->getMessage());
            }
            
        } else {
            exitJson(400, '', 'Bad Request', ' Request type error', '请求类型错误');
        }
    }


    /*新增、修改、删除商家
    *  isadmin 为1时直接审核通过
    */
    public function business()
    {
        $time = date('Y-m-d H:i:s',time());
        if (Request::instance()->isPost() || Request::instance()->isPut()) {
            

            if (Request::instance()->isPost()) {
                $validate = new Validate([
                    'businessname' => 'require',
                    'keyword' => 'require',
                    'address' => 'require',
                    'phone' => 'require|max:11|/^1[3-8]{1}[0-9]{9}$/',
                    'password'=> 'require|length:6,16',
                    'repassword'=> 'require|confirm:password'
                ]);

                $params = input('post.');
                $params['createtime'] = $time;

                //如果购买套餐，更新购买日期跟到期日期
                if (!empty($params['packageid'])) {
                    $params['buytime'] = date('Y-m-d', time());
                    $params['expiretime'] = date('Y-m-d', strtotime("+1 year"));
                }
            } else {
                $validate = new Validate([
                    'businessname' => 'require',
                    'keyword' => 'require',
                    'address' => 'require',
                    'phone' => 'require|max:11|/^1[3-8]{1}[0-9]{9}$/',
                    'password'=> 'length:6,16',
                    'repassword'=> 'confirm:password'
                ]);

                $params = input('put.');
                $params['updatetime'] = $time;

                $params['buytime'] = !empty($params['buytime'])?$params['buytime']:null;
                $params['expiretime'] = !empty($params['expiretime'])?$params['expiretime']:null;
            }

            if (!$validate->check($params)) {
                exitJson(400, '', "Data bad",'data not empty', $validate->getError());
            }
            // 如果是总后台添加，直接通过
            if (isset($params['isadmin']) && $params['isadmin'] == 1) {
                $params['status'] = 1;
            }

            $params['password'] = encrypt($params['password']);

            $this->_addEditBusiness($params);
        } else if (Request::instance()->isDelete()) {
            // 删除
        } else {
            exitJson(400, '', 'Bad Request', ' Request type error', '请求类型错误');
        }
    }
    
    private function _addEditBusiness($data)
    {
        Db::startTrans();
        try {
            $Business = new BusinessModel();
            if (isset($data['id']) && $data['id'] != '') {
                $log = "商家管理>商家列表>修改";
                $res = $Business->save($data,['id' => $data['id']]);
            } else {
                // 查看是否以重复数据
                $isRepeat = BusinessModel::get(['businessname' => $data['businessname']]);
                if($isRepeat) exitJson(409, '', 'Conflict', 'data already exists', $data['businessname'].'已存在');

                $log = "商家管理>商家列表>增加";
                $res = $Business->data($data)->save();
            }
            if ($res) {
                // 日志
                $this->adminLog($this->aid,$log,$this->role);
                Db::commit();

                $backData = BusinessModel::get($Business->id);
                exitJson(200,$backData);
            } else {
                exitJson(400,'', 'Bad Request', ' Request type error', '增加数据失败');
            }
        } catch (Exception $e) {
            Db::rollback();
            // throw new Exception($e->getCode(),$e->getMessage(),'');
            exitJson(400, '', 'Bad Request', ' Request type error', $e->getMessage());
        }
    }

    /* 商家套餐列表 method-get
    * @int pageIndex 页数
    * @int pageSize 条数
    * @int id 有ID就获取详情
    */
    public function business_package_list()
    {
        if (!Request::instance()->isGet()) exitJson(400, '', 'Bad Request', ' Request type error', '请求类型错误');
        
        $get = input('get.');
        $pageIndex = isset($get['pageIndex']) && $get['pageIndex'] !=''?$get['pageIndex']:1; // 页数
        $pageSize  = isset($get['pageSize']) && $get['pageSize'] !=''?$get['pageSize']:10; // 条数
        $limitStart = ($pageIndex-1)*$pageSize;

        $where = '1 ';

        // 如果有ID，则查询该id的详情
        $packageid = isset($get['id'])?$get['id']:'';
        if (isset($get['id']) && empty($packageid)) {
            exitJson(400, '', 'Bad Request', ' Request type error', '请求类型错误');
        } else if (isset($get['id']) && !empty($packageid)) {
            $res = BusinessPackage::get($packageid);
            exitJson(200,$res);
        }
        
        $BusinessPackage = new BusinessPackage();
        $res = $BusinessPackage->where($where)
            ->limit($limitStart, $pageSize)
            ->order('createtime', 'desc')
            ->select();
        if ($res) {
            exitJson(200,$res);
        } else {
            exitJson(404, '', 'Not Found', 'data does not exist', '数据不存在');
        }
    }

    /* 新增、修改、删除商家套餐
    * @string packagename method-post
    * @number packageprice
    * @number discount
    * @string desc
    * @string background_url
    * @int id 只有ID为删除 method-delete
    * @int ishide 显示/不显示，有id method-put
    */
    public function business_package()
    {
        if (Request::instance()->isPost()) {
            $params = input('post.');
            $data['id']           = isset($params['id'])?$params['id']:'';
            $data['packagename']  = isset($params['packagename'])?$params['packagename']:'';
            $data['packageprice'] = isset($params['packageprice'])?$params['packageprice']:'';
            $data['discount']     = isset($params['discount'])?$params['discount']:'';
            $data['desc']         = isset($params['desc'])?$params['desc']:'';
            $data['background_url'] = '';
            $data['createtime']   = date('Y-m-d H:i:s',time());

            if (empty($data['packagename']) || empty($data['packageprice']) || empty($data['discount'])) {
                exitJson(400, '', 'Bad Request', ' Data not null', ' 数据不能为空');
            }
            
            $this->_addEditBusinessPackage($data);
        } else if (Request::instance()->isPut()) {
            $params = input('put.');
            $data['id']     = isset($params['id'])?$params['id']:'';
            $data['ishide'] = isset($params['ishide'])?$params['ishide']:'';

            if (empty($data['id']) || empty($data['ishide'])) {
                exitJson(400, '', 'Bad Request', ' Data not null', ' 数据不能为空');
            }

            $this->_addEditBusinessPackage($data);

        } else if (Request::instance()->isDelete()) {
            $packageid = input('?delete.id')?input('delete.id'):'';
            if (empty($packageid)) exitJson(400, '', 'Bad Request', ' Data not null', ' 数据不能为空');

            $this->_delBusinessPackage($packageid);
        } else {
            exitJson(400, '', 'Bad Request', ' Request type error', '请求类型错误');
        }
    }

    private function _addEditBusinessPackage($data)
    {
        Db::startTrans();
        try {
            $BusinessPackage = new BusinessPackage();
            
            if ($data['id']) {
                $log = "商家管理>会员套餐>修改";
                $res = $BusinessPackage->save($data,['id' => $data['id']]);
            } else {
                // 查看是否以重复数据
                $isRepeat = BusinessPackage::get(['packagename' => $data['packagename'], 
                'packageprice' => $data['packageprice'],'discount' => $data['discount']
                ]);
                if($isRepeat) exitJson(409, '', 'Conflict', 'data already exists', $data['packagename'].'已存在');

                $log = "商家管理>会员套餐>增加";
                $res = $BusinessPackage->data($data)->save();
            }

            // 日志
            $this->adminLog($this->aid,$log,$this->role);
            Db::commit();

            $backData = BusinessPackage::get($BusinessPackage->id);
            exitJson(200,$backData);
        } catch (Exception $e) {
            Db::rollback();
            // throw new Exception($e->getCode(),$e->getMessage(),'');
            exitJson(400, '', 'Bad Request', ' Request type error', $e->getMessage());
        }
    }

    private function _delBusinessPackage($id)
    {
        Db::startTrans();
        try {
            BusinessPackage::destroy($id);
            // 日志
            $this->adminLog($this->aid, '商家管理>会员套餐>删除', $this->role);
            Db::commit();
            exitJson(200, '删除成功');
        } catch (Exception $e) {
            Db::rollback();
            // throw new Exception($e->getCode(),$e->getMessage(),'');
            exitJson(400, '', 'Bad Request', ' Request type error', $e->getMessage());
        }
    }

    /* 新增、修改、删除商家服务分类
    * @int id 有ID就是编辑，delete就是删除
    * @int pid 有pid就是二级分类
    * @int sort 排序，从小到大
    * @string catename 分类名称
    */
    public function business_cate()
    {
        $time = date('Y-m-d H:i:s',time());
        if (Request::instance()->isPost() || Request::instance()->isPut()) {
            if (Request::instance()->isPost()) {
                $data['createtime'] = $time;
                $params = input('post.');
            } else {
                $data['updatetime'] = $time;
                $params = input('put.');
            }
            
            $data['id']         = isset($params['id'])?$params['id']:'';
            $data['sort']       = $params['sort'];
            $data['pid']        = isset($params['pid'])?$params['pid'] : 0;
            $data['catename']   = $params['catename'];
            $data['img_url']    = '';

            if (empty($data['sort']) || empty($data['catename'])) {
                exitJson(400, '', 'Bad Request', ' Data not null', ' 数据不能为空');
            }
            
            $this->_addEditBussinessCate($data);
        } else if (Request::instance()->isDelete()) {
            // 删除
            $cateid = input('?delete.id')?input('delete.id'):'';
            if (empty($cateid)) exitJson(400, '', 'Bad Request', ' Data not null', ' 数据不能为空');

            $this->_deleteBusinessCate($cateid);
        } else {
            
            exitJson(400, '', 'Bad Request', ' Request type error', '请求类型错误');
        }
    }

    /* 查询商家服务分类
    * @int pageIndex 页数
    * @int pageSize 条数
    * @int id     如果有ID就查询其的二级分类 
    */
    public function bussiness_cate_list()
    {
        if (!Request::instance()->isGet()) exitJson(400, '', 'Bad Request', ' Request type error', '请求类型错误');
        
        $get = input('get.');
        $pageIndex = isset($get['pageIndex']) && $get['pageIndex'] !=''?$get['pageIndex']:1; // 页数
        $pageSize  = isset($get['pageSize']) && $get['pageSize'] !=''?$get['pageSize']:10; // 条数
        $limitStart = ($pageIndex-1)*$pageSize;

        $where = '1 ';

        // 如果有ID，则查询该id的二级分类
        $pid = isset($get['id'])?$get['id']:'';
        if (isset($get['id']) && empty($pid)) {
            exitJson(400, '', 'Bad Request', ' Request type error', '请求类型错误');
        } else if (isset($get['id']) && !empty($pid)) {
            $where = ['pid' => $pid];
        }
        
        $BusinessCate = new BusinessCate();
        $res = $BusinessCate->where($where)
            ->limit($limitStart, $pageSize)
            ->order('sort', 'asc')
            ->select();
        if ($res) {
            exitJson(200,$res);
        } else {
            exitJson(404, '', 'Not Found', 'data does not exist', '数据不存在');
        }
    }

    private function _addEditBussinessCate($data)
    {
        Db::startTrans();
        try {
            $BusinessCate = new BusinessCate();

            // 查看是否以重复数据
            $isRepeat = BusinessCate::get(['sort' => $data['sort'], 'catename' => $data['catename']]);
            if($isRepeat) exitJson(409, '', 'Conflict', 'data already exists', $data['catename'].'已存在');

            if ($data['id']) {
                $log = "首页管理>服务分类>修改";
                $res = $BusinessCate->save($data,['id' => $data['id']]);
            } else {
                $log = "首页管理>服务分类>增加";
                $res = $BusinessCate->data($data)->save();
            }
            
            if ($res) {
                $backData = BusinessCate::get($BusinessCate->id);
                // 日志
                $this->adminLog($this->aid, $log, $this->role);
                Db::commit();
                exitJson(200,$backData);
            }
        } catch (Exception $e) {
            Db::rollback();
            // throw new Exception($e->getCode(),$e->getMessage(),'');
            exitJson(400, '', 'Bad Request', ' Request type error', $e->getMessage());
        }
    }

    private function _deleteBusinessCate($id)
    {
        Db::startTrans();
        try {
            BusinessCate::destroy($id);
            // 日志
            $this->adminLog($this->aid, '首页管理>服务分类>删除', $this->role);
            Db::commit();
            exitJson(200, '删除成功');
        } catch (Exception $e) {
            Db::rollback();
            // throw new Exception($e->getCode(),$e->getMessage(),'');
            exitJson(400, '', 'Bad Request', ' Request type error', $e->getMessage());
        }
    }
}
