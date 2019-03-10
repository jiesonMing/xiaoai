<?php
namespace app\xiaoai\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\Validate;
use app\xiaoai\model\GoodsModel;
use app\xiaoai\model\GoodsCate;
use app\xiaoai\model\GoodsAttr;
use app\xiaoai\model\GoodsGroup;
use app\xiaoai\model\GoodsGroupTemp;
use app\xiaoai\model\GoodsShuffling;

use app\common\exception\Exception;


/*
* jieson 2019.03.04
* 后台商品管理接口
*/

class Goods extends Base
{
    public $aid = 0; // 管理员id
    public $role = ''; // 管理员角色
    
    public function _initialize() {      
        parent::_initialize();
        $this->aid = Session::get('aid');
        $this->role = '管理员'; // 默认
    }

    /* 商品查询
    * 审核成功，待审核，失败
    */
    public function goods_list()
    {

    }

    /* 商品增删改
    *
    */
    public function goods()
    {

    }
    
    /* 商品审核
    */
    public function goods_check()
    {

    }

    /* 商品拼团规格
    */
    public function goods_group_list()
    {
        if (!Request::instance()->isGet()) exitJson(400, '', 'Bad Request', ' Request type error', '请求类型错误');
        
        $get = input('get.');
        $pageIndex = isset($get['pageIndex']) && $get['pageIndex'] !=''?$get['pageIndex']:1; // 页数
        $pageSize  = isset($get['pageSize']) && $get['pageSize'] !=''?$get['pageSize']:10; // 条数
        $limitStart = ($pageIndex-1)*$pageSize;
        
        if (isset($get['id']) && !empty($get['id'])) {
            $res = GoodsGroup::get(['id' => $get['id']]);
        } else {
            $GoodsGroup = new GoodsGroup();
            $res = $GoodsGroup
                ->limit($limitStart, $pageSize)
                ->order('createtime', 'desc')
                ->select();
        }
        
        if ($res) {
            exitJson(200,$res);
        } else {
            exitJson(404, '', 'Not Found', 'data does not exist', '数据不存在');
        }
    }

    /* 商品拼团增删改
    */
    public function goods_group()
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
            $data['person']     = $params['person'];
            $data['discount']   = isset($params['discount'])?$params['discount'] : 0;

            if (empty($data['person']) || empty($data['discount'])) {
                exitJson(400, '', 'Bad Request', ' Data not null', ' 数据不能为空');
            }
            $data['request_type'] = 'addEdit';

            $this->_addEditDelGoodsGroup($data);
        } else if (Request::instance()->isDelete()) {
            // 删除
            $id = input('?delete.id')?input('delete.id'):'';
            if (empty($id)) exitJson(400, '', 'Bad Request', ' Data not null', ' 数据不能为空');

            $data['id']           =  $id;
            $data['request_type'] = 'del';

            $this->_addEditDelGoodsGroup($data);
        } else {
            
            exitJson(400, '', 'Bad Request', ' Request type error', '请求类型错误');
        }
    }

    private function _addEditDelGoodsGroup($data)
    {
        Db::startTrans();
        try {
            $GoodsGroup = new GoodsGroup();
            switch ($data['request_type']) {
                case 'addEdit':
                    // 查看是否以重复数据
                    $isRepeat = GoodsGroup::get(['person' => $data['person'], 'discount' => $data['discount']]);
                    if($isRepeat) exitJson(409, '', 'Conflict', 'data already exists', "人数{$data['person']}折扣{$data['discount']}已存在");

                    if ($data['id']) {
                        $log = "商品管理>商品分类>修改";
                        $res = $GoodsGroup->save($data,['id' => $data['id']]);
                    } else {
                        $log = "商品管理>商品分类>增加";
                        $res = $GoodsGroup->data($data)->save();
                    }
                    
                    if ($res) {
                        $backData = GoodsGroup::get($GoodsGroup->id);
                        // 日志
                        $this->adminLog($this->aid, $log, $this->role);
                        Db::commit();
                        exitJson(200,$backData);
                    } else {
                        Db::rollback();
                        exitJson(400, '', 'Bad operation', ' data type error', '请求数据有误');
                    }
                    break;

                case 'del':
                    GoodsGroup::destroy($data['id']);
                    // 日志
                    $this->adminLog($this->aid, '商品管理>拼团与规格>删除', $this->role);
                    Db::commit();
                    exitJson(200, '删除成功');
                    break;

                default:
                    Db::rollback();
                    exitJson(400, '', 'Bad Request', ' Request type error', '请求数据有误');
                    break;
            }
        } catch (Exception $e) {
            Db::rollback();
            exitJson(400, '', 'Bad Request', ' Request type error', $e->getMessage());
        }
    }

    /* 商品分类查询
    * @int pageIndex 页数
    * @int pageSize 条数
    * @int id     如果有ID就查询其的二级分类 
    */
    public function goods_cate_list()
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
        
        $GoodsCate = new GoodsCate();
        $res = $GoodsCate->where($where)
            ->limit($limitStart, $pageSize)
            ->order('sort', 'asc')
            ->select();
        if ($res) {
            exitJson(200,$res);
        } else {
            exitJson(404, '', 'Not Found', 'data does not exist', '数据不存在');
        }
    }

    /* 商品分类增删改
    *
    */
    public function goods_cate()
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
            $data['goodscatename']   = $params['goodscatename'];

            if (empty($data['sort']) || empty($data['goodscatename'])) {
                exitJson(400, '', 'Bad Request', ' Data not null', ' 数据不能为空');
            }
            
            $this->_addEditGoodsCate($data);
        } else if (Request::instance()->isDelete()) {
            // 删除
            $cateid = input('?delete.id')?input('delete.id'):'';
            if (empty($cateid)) exitJson(400, '', 'Bad Request', ' Data not null', ' 数据不能为空');

            $this->_deleteGoodsCate($cateid);
        } else {
            
            exitJson(400, '', 'Bad Request', ' Request type error', '请求类型错误');
        }
    }

    private function _addEditGoodsCate($data)
    {
        Db::startTrans();
        try {
            $GoodsCate = new GoodsCate();

            // 查看是否以重复数据
            $isRepeat = GoodsCate::get(['sort' => $data['sort'], 'goodscatename' => $data['goodscatename']]);
            if($isRepeat) exitJson(409, '', 'Conflict', 'data already exists', $data['goodscatename'].'已存在');

            if ($data['id']) {
                $log = "商品管理>商品分类>修改";
                $res = $GoodsCate->save($data,['id' => $data['id']]);
            } else {
                $log = "商品管理>商品分类>增加";
                $res = $GoodsCate->data($data)->save();
            }
            
            if ($res) {
                $backData = GoodsCate::get($GoodsCate->id);
                // 日志
                $this->adminLog($this->aid, $log, $this->role);
                Db::commit();
                exitJson(200,$backData);
            }
        } catch (Exception $e) {
            Db::rollback();
            exitJson(400, '', 'Bad Request', ' Request type error', $e->getMessage());
        }
    }

    private function _deleteGoodsCate($id)
    {
        Db::startTrans();
        try {
            GoodsCate::destroy($id);
            // 日志
            $this->adminLog($this->aid, '商品管理>商品分类>删除', $this->role);
            Db::commit();
            exitJson(200, '删除成功');
        } catch (Exception $e) {
            Db::rollback();
            exitJson(400, '', 'Bad Request', ' Request type error', $e->getMessage());
        }
    }

}