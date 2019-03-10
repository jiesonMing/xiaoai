<?php
namespace app\xiaoai\controller;
use think\Db;
use think\Session;
use think\Request;
use app\xiaoai\model\UsersModel;

/*
* jieson 2019.03.07
* 后台用户管理接口
*/

class Users extends Base
{
    public $aid = 0; // 管理员id
    public $role = ''; // 管理员角色
    
    public function _initialize() {      
        parent::_initialize();
        $this->aid = Session::get('aid');
        $this->role = '管理员'; // 默认
    }

    /* 用户列表
    * @string startTime、endTime
    * @int isbusiness 0全部, 1商家用户, 2正常用户
    */
    public function users_list()
    {
        if (!Request::instance()->isGet()) exitJson(400, '', 'Bad Request', ' Request type error', '请求类型错误');
        
        $get = input('get.');
        $searchWord = isset($get['search'])?$get['search']:'';
        $isbusiness = isset($get['isbusiness'])?$get['isbusiness']:'';// 0全部 ,1商家用户 ,2正常用户

        $pageIndex = isset($get['pageIndex']) && $get['pageIndex'] !=''?$get['pageIndex']:1; // 页数
        $pageSize  = isset($get['pageSize']) && $get['pageSize'] !=''?$get['pageSize']:10; // 条数
        $limitStart = ($pageIndex-1)*$pageSize;

        $where = '1 ';
        $where.= isset($get['startTime']) && $get['startTime'] !=''? " and createtime > ".$get['startTime']:'';
        $where.= isset($get['endTime']) && $get['endTime'] !=''? " and createtime < ".$get['endTime']:'';
        $where.= $searchWord!=''? " and concat(username,phone) like '%".$searchWord."%'" : '';
        if ($isbusiness ==0 || $isbusiness == '') {
            $where .= " and isbusiness in(0,1)";
        } else if ($isbusiness == 1) {
            $where .= " and isbusiness = 1";
        } else if ($isbusiness == 2) {
            $where .= " and isbusiness = 0";
        } else {
            $where .= " and isbusiness = -1";
        }

        if (isset($get['id']) && !empty($get['id'])) {
            $users = UsersModel::get(['uid' => $get['id']]);
            $res = $users->getUsersById($get['id']);
        } else {
            $users = new UsersModel();
            $res = $users->where($where)
                ->limit($limitStart, $pageSize)
                ->order('uid', 'desc')
                ->select();
        }
        
        if ($res) {
            exitJson(200,$res);
        } else {
            exitJson(404, '', 'Not Found', 'data does not exist', '数据不存在');
        }
    }

    /* 用户数据
    * @int day时间参数，0今天，7为7天，30为30天
    * @string startTime、endTime
    * @int isbusiness 0全部 ,1商家用户 ,2正常用户
    */
    public function users_data_list()
    {
        if (!Request::instance()->isGet()) exitJson(400, '', 'Bad Request', ' Request type error', '请求类型错误');
        
        $get = input('get.');
        $isbusiness = isset($get['isbusiness'])?$get['isbusiness']:'';// 0全部 ,1商家用户 ,2正常用户

        $day = isset($get['day'])?$get['day']:'';

        $where = '1';
        $where.= isset($get['startTime']) && $get['startTime'] !=''? " and createtime > ".$get['startTime']:'';
        $where.= isset($get['endTime']) && $get['endTime'] !=''? " and createtime < ".$get['endTime']:'';

        if ($day == '') {
            // 不选择天的话，默认是所有？
            $where.= ' ';
        } else if ($day == 0) {
            $where.= " and DateDiff(now(),createtime)=0";
        } else if ($day == 7) {
            $where.= " and DateDiff(now(),createtime)<=7";
        } else if ($day == 30) {
            $where.= " and DateDiff(now(),createtime)<=30";
        }

        $today = 'DateDiff(now(),createtime)=0';
        $todayActive = 'DateDiff(now(),lastlogintime)=0';

        $res = [];
        if ($isbusiness ==0 || $isbusiness == '') {
            // 全部
            $res = array_merge($this->_businessData($where,$today,$todayActive,$day), $this->_usersData($where,$today,$todayActive,$day));
        } else if ($isbusiness == 1) {
            // 商家用户
            $res = $this->_businessData($where,$today,$todayActive,$day);
        } else if ($isbusiness == 2) {
            // 正常用户
            $res = $this->_usersData($where,$today,$todayActive,$day);
        }
                
        if ($res) {
            exitJson(200,$res);
        } else {
            exitJson(404, '', 'Not Found', 'data does not exist', '数据不存在');
        }
    }

    // 正常用户数据
    private function _usersData($where, $today, $todayActive, $day)
    {
        $res = [];
        $res['totalUserNumber']    = db('users')->where($where)->count('uid');
        $res['todayAddUserNumber'] = db('users')->where($today)->count('uid');
        // 活跃用户按天 
        $res['activeUserNumber']   = db('users')->where($todayActive)->count('uid');

        // 每天活跃总数
        $res['activeUser']     = Db::query("SELECT
                count(uid) as totalNumber,DATE_FORMAT(lastlogintime,'%Y-%m-%d') as time
            FROM
                users
            WHERE
                DATE_SUB(CURDATE(), INTERVAL $day DAY) <= date(lastlogintime)
            GROUP BY
            lastlogintime
            order BY
            lastlogintime asc");

        // 每天新增总数
        $res['addUser']     = Db::query("SELECT
                count(uid) as totalNumber,DATE_FORMAT(createtime,'%Y-%m-%d') as time
            FROM
                users
            WHERE
                DATE_SUB(CURDATE(), INTERVAL  $day DAY) <= date(createtime)
            GROUP BY
            createtime
            order BY
            createtime asc");

        return $res;
    }

    // 商家数据
    private function _businessData($where, $today, $todayActive, $day)
    {
        $res = [];
        $res['totalBusinessnumber']    = db('business')->where($where)->count('id');
        $res['todayAddBusinessNumber'] = db('business')->where($today)->count('id');
        $res['activeBusinessNumber']   = db('business')->where($todayActive)->count('id');

        // 每天活跃总数
        $res['activeBusiness']     = Db::query("SELECT
                count(id) as totalNumber,DATE_FORMAT(lastlogintime,'%Y-%m-%d') as time
            FROM
                business
            WHERE
                DATE_SUB(CURDATE(), INTERVAL $day DAY) <= date(lastlogintime)
            GROUP BY
                lastlogintime
            order BY
                lastlogintime asc");

        // 每天新增总数
        $res['addBusiness']     = Db::query("SELECT
                count(id) as totalNumber,DATE_FORMAT(createtime,'%Y-%m-%d') as time
            FROM
                business
            WHERE
                DATE_SUB(CURDATE(), INTERVAL  $day DAY) <= date(createtime)
            GROUP BY
                createtime
            order BY
                createtime asc");

        return $res;
    }
}