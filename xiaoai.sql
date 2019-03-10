/*
 Navicat MySQL Data Transfer

 Source Server         : 本地
 Source Server Type    : MySQL
 Source Server Version : 80015
 Source Host           : localhost:3306
 Source Schema         : xiaoai

 Target Server Type    : MySQL
 Target Server Version : 80015
 File Encoding         : 65001

 Date: 10/03/2019 21:17:09
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for admin
-- ----------------------------
DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `account` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '管理员账号',
  `password` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '密码，2个md5加密',
  `token` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '登陆之后更新的token,md5账号+密码',
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '管理员姓名',
  `phone` char(11) NOT NULL DEFAULT '' COMMENT '手机号码',
  `rolesid` int(10) NOT NULL DEFAULT '0' COMMENT '管理身份(权限),0为超管',
  `createtime` datetime DEFAULT NULL COMMENT '创建时间',
  `updatetime` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='管理员表';

-- ----------------------------
-- Table structure for admin_log
-- ----------------------------
DROP TABLE IF EXISTS `admin_log`;
CREATE TABLE `admin_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `aid` int(11) NOT NULL DEFAULT '0' COMMENT '管理员id',
  `module` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '操作的模块',
  `role` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '管理员角色',
  `createtime` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8 COMMENT='管理员日志表';

-- ----------------------------
-- Table structure for business
-- ----------------------------
DROP TABLE IF EXISTS `business`;
CREATE TABLE `business` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `businessname` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商家名称',
  `registname` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '注册人',
  `cateid` int(11) NOT NULL DEFAULT '0' COMMENT '商家服务分类id',
  `packageid` int(11) NOT NULL DEFAULT '0' COMMENT '商家套餐id',
  `buytime` datetime DEFAULT NULL COMMENT '套餐购买时间',
  `expiretime` datetime DEFAULT NULL COMMENT '套餐到期时间',
  `keyword` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商家关键字',
  `provinceid` int(11) NOT NULL DEFAULT '0' COMMENT '省id',
  `cityid` int(11) NOT NULL DEFAULT '0' COMMENT '城市id',
  `areaid` int(11) NOT NULL DEFAULT '0' COMMENT '区id',
  `address` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '详细地址',
  `opentime` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '营业时间-开门时间',
  `closetime` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '营业时间-关门时间',
  `announcement` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商家公告',
  `logo_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商家logo',
  `checkoutwechat_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '结账微信',
  `license_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '营业执照',
  `other_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '办学许可及其他',
  `msg_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商家信息图',
  `introduce` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商家介绍',
  `phone` char(11) NOT NULL DEFAULT '' COMMENT '手机号码',
  `password` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '密码，md5加密',
  `status` tinyint(4) DEFAULT '0' COMMENT '商家状态,0待审核，1通过，2拒绝',
  `remark` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '备注/理由',
  `ishide` tinyint(4) DEFAULT '0' COMMENT '是否屏蔽商家，1屏蔽',
  `createtime` datetime DEFAULT NULL COMMENT '创建时间',
  `updatetime` datetime DEFAULT NULL COMMENT '修改时间',
  `provinces` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '省市区',
  `lastlogintime` datetime DEFAULT NULL COMMENT '最后登陆时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='商家表';

-- ----------------------------
-- Table structure for business_cate
-- ----------------------------
DROP TABLE IF EXISTS `business_cate`;
CREATE TABLE `business_cate` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `sort` int(10) DEFAULT NULL COMMENT '排序，从小到大',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '父级id, 为0表示是一级分类',
  `catename` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '分类名称',
  `img_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '服务分类图标',
  `createtime` datetime DEFAULT NULL COMMENT '创建时间',
  `updatetime` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COMMENT='商家服务分类表';

-- ----------------------------
-- Table structure for business_package
-- ----------------------------
DROP TABLE IF EXISTS `business_package`;
CREATE TABLE `business_package` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `packagename` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '套餐名称',
  `packageprice` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '会员价格，元/年',
  `discount` tinyint(6) NOT NULL DEFAULT '0' COMMENT '结算折扣，%',
  `desc` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '套餐福利描述',
  `background_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '套餐背景',
  `ishide` tinyint(4) DEFAULT '0' COMMENT '0显示，1不显示',
  `createtime` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='商家套餐表';

-- ----------------------------
-- Table structure for business_renewal
-- ----------------------------
DROP TABLE IF EXISTS `business_renewal`;
CREATE TABLE `business_renewal` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `bid` int(11) NOT NULL DEFAULT '0' COMMENT '商家id',
  `packageid` int(10) NOT NULL DEFAULT '0' COMMENT '升级续费套餐id',
  `status` tinyint(4) DEFAULT '0' COMMENT '商家状态,0待审核，1通过，2拒绝',
  `remark` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '备注/理由',
  `createtime` datetime DEFAULT NULL COMMENT '创建时间',
  `updatetime` datetime DEFAULT NULL COMMENT '修改时间',
  `payvoucher_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '支付凭证',
  `oldpackageid` int(10) NOT NULL DEFAULT '0' COMMENT '原来套餐id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='商家套餐续费表';

-- ----------------------------
-- Table structure for goods
-- ----------------------------
DROP TABLE IF EXISTS `goods`;
CREATE TABLE `goods` (
  `goodsid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '商品主键ID',
  `goodsname` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品名称',
  `manufacturer` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '厂家',
  `introduce` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品简介',
  `freight` int(11) NOT NULL DEFAULT '0' COMMENT '运费',
  `sales` int(11) DEFAULT '0' COMMENT '商品销量',
  `cover_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品封面图',
  `sort` int(10) DEFAULT NULL COMMENT '排序，从小到大',
  `cateid` int(11) NOT NULL DEFAULT '0' COMMENT '分类ID',
  `video_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品视频地址',
  `content` text COMMENT '商品详情',
  `type` tinyint(2) NOT NULL DEFAULT '1' COMMENT '商品类型，1实物，2课程视频，3线下视频',
  `isgroup` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否拼团，默认不拼团，1拼团',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '商品状态，0待审核，1正常，2失败',
  `remark` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '备注',
  `createtime` datetime DEFAULT NULL COMMENT '创建时间',
  `updatetime` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`goodsid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品表';

-- ----------------------------
-- Table structure for goods_attr
-- ----------------------------
DROP TABLE IF EXISTS `goods_attr`;
CREATE TABLE `goods_attr` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '规格id',
  `goods_id` int(11) NOT NULL COMMENT '商品的id',
  `goods_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品名称',
  `goods_code` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品编码，自动生成',
  `goods_spec` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品规格',
  `goods_price` decimal(10,2) DEFAULT '0.00' COMMENT '售价',
  `goods_num` int(11) NOT NULL DEFAULT '0' COMMENT '库存',
  `isdefault` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否默认，1默认',
  `isonsale` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否上架，1上架',
  `remark` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '备注',
  `createtime` datetime DEFAULT NULL COMMENT '创建时间',
  `updatetime` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品规格表';

-- ----------------------------
-- Table structure for goods_cate
-- ----------------------------
DROP TABLE IF EXISTS `goods_cate`;
CREATE TABLE `goods_cate` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `sort` int(10) DEFAULT NULL COMMENT '排序，从小到大',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '父级id, 为0表示是一级分类',
  `goodscatename` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '分类名称',
  `createtime` datetime DEFAULT NULL COMMENT '创建时间',
  `updatetime` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='商品分类表';

-- ----------------------------
-- Table structure for goods_group
-- ----------------------------
DROP TABLE IF EXISTS `goods_group`;
CREATE TABLE `goods_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `person` int(11) NOT NULL DEFAULT '0' COMMENT '人数',
  `discount` decimal(10,1) NOT NULL DEFAULT '0.0' COMMENT '折扣，9折',
  `createtime` datetime DEFAULT NULL COMMENT '创建时间',
  `updatetime` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='商品拼团规格表';

-- ----------------------------
-- Table structure for goods_shuffling
-- ----------------------------
DROP TABLE IF EXISTS `goods_shuffling`;
CREATE TABLE `goods_shuffling` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '规格id',
  `goods_id` int(11) NOT NULL COMMENT '商品的id',
  `img_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '轮播图地址',
  `createtime` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品轮播图表';

-- ----------------------------
-- Table structure for smscode
-- ----------------------------
DROP TABLE IF EXISTS `smscode`;
CREATE TABLE `smscode` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `phone` char(11) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '手机号',
  `code` int(10) NOT NULL DEFAULT '0' COMMENT '验证码',
  `createtime` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='短信验证码发送表';

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `uid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户id',
  `username` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户名',
  `email` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '邮箱',
  `phone` char(11) NOT NULL DEFAULT '' COMMENT '手机号码',
  `wxname` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户微信昵称',
  `wxopenid` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '微信的openid',
  `img_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户头像',
  `createtime` datetime DEFAULT NULL COMMENT '创建时间',
  `updatetime` datetime DEFAULT NULL COMMENT '修改时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '用户状态',
  `isdelete` tinyint(4) NOT NULL DEFAULT '0' COMMENT '删除情况，1删除',
  `isbusiness` tinyint(4) NOT NULL DEFAULT '0' COMMENT '1表示为商家入驻',
  `bid` int(11) NOT NULL DEFAULT '0' COMMENT '商家入驻后的商家ID',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户总金额',
  `frozenamount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '冻结金额',
  `useamount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '可用余额',
  `paypassword` char(6) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '支付密码',
  `withdrawalamount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '已经提现金额',
  `sex` tinyint(2) DEFAULT '0' COMMENT '性别，默认0，1男，2女',
  `lastlogintime` datetime DEFAULT NULL COMMENT '最后登陆时间',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=1015 DEFAULT CHARSET=utf8 COMMENT='用户表';

SET FOREIGN_KEY_CHECKS = 1;
