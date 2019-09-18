-- --------------------------------------------------------
-- 主机:                           192.168.9.112
-- 服务器版本:                        5.6.39 - MySQL Community Server (GPL)
-- 服务器操作系统:                      Linux
-- HeidiSQL 版本:                  9.4.0.5125
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- 导出  事件 vote_mp.event_pro_admin_vote 结构
DELIMITER //
CREATE DEFINER=`mp`@`%` EVENT `event_pro_admin_vote` ON SCHEDULE EVERY 20 SECOND STARTS '2018-05-23 16:50:24' ON COMPLETION PRESERVE ENABLE COMMENT '投票生成器事件' DO call pro_tongsheng_admin_vote//
DELIMITER ;

-- 导出  事件 vote_mp.event_pro_black_vote 结构
DELIMITER //
CREATE DEFINER=`mp`@`%` EVENT `event_pro_black_vote` ON SCHEDULE EVERY 10 MINUTE STARTS '2018-05-24 17:49:42' ON COMPLETION PRESERVE ENABLE COMMENT '刷票黑名单事件' DO call pro_tongsheng_black_vote//
DELIMITER ;

-- 导出  过程 vote_mp.pro_tongsheng_admin_vote 结构
DELIMITER //
CREATE DEFINER=`mp`@`%` PROCEDURE `pro_tongsheng_admin_vote`()
    COMMENT '投票生成器'
BEGIN
	-- marker表ID
DECLARE d_id INT;

-- 报名人ID
DECLARE d_sign_id INT;

-- 投票开始时间戳
DECLARE d_start_time INT;

-- 投票结束时间戳
DECLARE d_stop_time INT;

-- 当前时间戳
DECLARE d_now_time INT;

-- 总需要长的票数
DECLARE d_vote_count INT DEFAULT 0;

-- 已经涨的票数
DECLARE d_vote_current INT DEFAULT 0;

-- 每次上涨票数的平均数
DECLARE d_vote_avg INT DEFAULT 0;

-- 本次上涨票数值
DECLARE d_vote_this INT DEFAULT 0;

-- 变更数量统计
DECLARE n_change INT DEFAULT 0;

-- 游标结束标识设置
DECLARE done INT DEFAULT 0;

-- 刷票工单
-- 游标定义
DECLARE cur1 CURSOR FOR SELECT
	t.id,
	t.sign_id,
	t.start_time,
	t.stop_time,
	t.vote_count,
	t.vote_current,
	UNIX_TIMESTAMP(NOW()),
	t.vote_per_sec
FROM
	wangluo_vote_maker t
WHERE
	t.is_finish = 0
AND t.is_delete = 0
AND t.start_time <= UNIX_TIMESTAMP(NOW());

-- 初始化游标循环结束值
DECLARE CONTINUE HANDLER FOR NOT FOUND
SET done = 1;

SELECT
	count(1) INTO n_change
FROM
	wangluo_vote_maker t
WHERE
	t.is_finish = 0
AND t.is_delete = 0
AND t.start_time <= UNIX_TIMESTAMP(NOW());

IF n_change > 0 THEN


	-- 打开游标
	OPEN cur1;

emp_loop :
LOOP
	FETCH cur1 INTO d_id,
	d_sign_id,
	d_start_time,
	d_stop_time,
	d_vote_count,
	d_vote_current,
	d_now_time,
	d_vote_avg;

IF done = 1 THEN
	LEAVE emp_loop;
END
IF;

-- 当已投票数量小于全部数量时，进入循环
IF d_vote_current < d_vote_count THEN

	-- 当剩余数量比平均值数量小时，采用当前剩余数量，否则使用平均值进行增长
IF (
	d_vote_count - d_vote_current
) < d_vote_avg THEN
	SELECT
		d_vote_count - d_vote_current INTO d_vote_this;


ELSE
	SELECT
		d_vote_avg INTO d_vote_this;


END
IF;
-- 更新票数信息
UPDATE wangluo_activity_sign t
SET t.admin_count = t.admin_count+d_vote_this,
 t.total_count = t.total_count+d_vote_this
WHERE
	t.id = d_sign_id;


-- 更新票数信息
UPDATE wangluo_activity t
SET t.total_count = t.total_count+d_vote_this,
t.vote_count = t.vote_count+d_vote_this
WHERE
	t.id = 1;
-- 更新刷票信息表投票数量和投票状态
IF d_vote_this + d_vote_current = d_vote_count THEN
	-- 更新完成标识
	UPDATE wangluo_vote_maker t
SET t.vote_current = t.vote_current + d_vote_this,
 t.is_finish = 1
WHERE
	t.id = d_id;


ELSE
	--  只更新数量
	UPDATE wangluo_vote_maker t
SET t.vote_current = t.vote_current + d_vote_this
WHERE
	t.id = d_id;


END
IF;


END
IF;





END
LOOP
	emp_loop;


END
IF;

END//
DELIMITER ;

-- 导出  过程 vote_mp.pro_tongsheng_black_vote 结构
DELIMITER //
CREATE DEFINER=`mp`@`%` PROCEDURE `pro_tongsheng_black_vote`()
    COMMENT '黑名单检测'
BEGIN
DECLARE do_time INT;
DECLARE do_warnning_minut_time INT;
DECLARE do_warnning_hour_time INT;
DECLARE do_warnning_minut_num INT DEFAULT 60;
DECLARE do_warnning_num INT DEFAULT 370;
DECLARE do_lock_time INT DEFAULT 9000;

SELECT
	UNIX_TIMESTAMP(NOW()) INTO do_time;

SELECT
	UNIX_TIMESTAMP(NOW()) - 600 INTO do_warnning_minut_time;
SELECT
	UNIX_TIMESTAMP(NOW()) - 3600 INTO do_warnning_hour_time;

-- 插入预警数据表，待PHP接口回调更新相关IP所在省市区县后进行黑名单判断
INSERT INTO wangluo_vote_warnning (
	`create_time`,
	`vote_id`,
	`vote_create_time`,
	`sign_id`,
	`appid`,
	`activity_id`,
	`voter_openid`,
	`voter_ip`
) SELECT
	do_time,
	t.id,
	t.create_time,
	t.sign_id,
	t.appid,
	t.activity_id,
	t.voter_openid,
	t.voter_ip
FROM
	wangluo_vote t
WHERE
	t.create_time > do_warnning_minut_time
AND t.sign_id IN (
	SELECT
		m.sign_id
	FROM
		wangluo_vote m
	WHERE
		m.create_time > do_warnning_minut_time
	GROUP BY
		m.sign_id
	HAVING
		count(1) > do_warnning_minut_num
);

-- 进行黑名单判断插入黑名单
-- 根据IP进行统计，确认哪些sign_id是黑名单数据进行处理。
INSERT INTO wangluo_black_id (
	`create_time`,
	`update_time`,
	`sign_id`,
	`activity_id`,
	`stoptime`,
	`status`,
	`vote_num`
) SELECT
	do_time,
	do_time,
	t.id,
	t.activity_id,
	do_time,
	0,
	count(1)
FROM
	wangluo_activity_sign t,
	wangluo_vote_warnning m
WHERE
	t.id = m.sign_id
AND m.create_time >= 	do_warnning_hour_time
AND m.sign_id IN (
	SELECT
		k.sign_id
	FROM
		wangluo_vote_warnning k
	WHERE
		k.create_time >= 	do_warnning_hour_time
	GROUP BY
		k.sign_id
	HAVING
		count(1) > do_warnning_num
)
AND t.id NOT IN (
	SELECT
		m.sign_id
	FROM
		wangluo_vote_maker m
)
AND t.id NOT IN (
	SELECT
		l.sign_id
	FROM
		wangluo_black_id l
	WHERE
		l. STATUS = 1
)
group by t.id,t.activity_id;
-- 解锁两个小时之前锁定的用户
UPDATE wangluo_activity_sign t,
 wangluo_black_id m
SET m. STATUS = 2,
 t.is_lock = 0,
 t.update_time=do_time
WHERE
	t.id = m.sign_id
and  t.is_lock = 1
and m. STATUS = 1
and m.stoptime<= do_time - do_lock_time;


-- 锁定黑名单用户2个小时
UPDATE wangluo_activity_sign t,
 wangluo_black_id m
SET m. STATUS = 1,
 m.stoptime = do_time,
 t.is_lock = 1
WHERE
	t.id = m.sign_id
and t.is_lock=0
and m.STATUS = 0;


END//
DELIMITER ;

-- 导出  表 vote_mp.wangluo_access 结构
CREATE TABLE IF NOT EXISTS `wangluo_access` (
  `node_id` int(11) unsigned NOT NULL COMMENT '节点ID',
  `role_id` int(11) unsigned NOT NULL COMMENT '角色ID',
  KEY `role_id` (`role_id`) USING BTREE,
  KEY `node_id` (`node_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='权限';

-- 数据导出被取消选择。
-- 导出  表 vote_mp.wangluo_activity 结构
CREATE TABLE IF NOT EXISTS `wangluo_activity` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `create_time` int(11) unsigned NOT NULL COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL COMMENT '更新时间',
  `is_delete` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除(0:否 1:是)',
  `user_id` int(11) DEFAULT '0' COMMENT '注册人员的用户ID（0代表是后台发起的活动）',
  `activity_name` varchar(30) NOT NULL COMMENT '活动名称',
  `activity_image` varchar(200) NOT NULL COMMENT '活动背景图',
  `apply_start_time` int(11) NOT NULL COMMENT '报名开始时间',
  `apply_stop_time` int(11) NOT NULL COMMENT '报名结束时间',
  `start_time` int(11) NOT NULL COMMENT '活动开始时间',
  `stop_time` int(11) NOT NULL COMMENT '活动结束时间',
  `activity_type` tinyint(1) NOT NULL COMMENT '活动类型(1个人/2商家/3景物)',
  `rule_id` int(11) DEFAULT NULL COMMENT '活动规则表ID',
  `audit_flag` tinyint(1) NOT NULL DEFAULT '0' COMMENT '报名是否需要审核0.否1.是',
  `gift_ids` text COMMENT '礼物ID',
  `theme_color` varchar(50) DEFAULT NULL COMMENT '活动主题颜色',
  `check_color` varchar(255) DEFAULT NULL COMMENT '选中后采用的颜色',
  `pay_background_image` varchar(255) DEFAULT NULL COMMENT '支付背景图',
  `activity_desc` longtext COMMENT '活动说明',
  `vote_bottom` int(10) DEFAULT NULL COMMENT '上榜最低票数',
  `receive_side` int(10) DEFAULT NULL COMMENT '收款方类型(1.平台2.自定义)',
  `pay_appid` varchar(255) DEFAULT NULL COMMENT '收款公众号的appid',
  `pay_mchid` varchar(255) DEFAULT NULL COMMENT '收款商户号',
  `pay_key` varchar(255) DEFAULT NULL COMMENT '收款密钥',
  `pay_appsecret` varchar(255) DEFAULT NULL COMMENT '收款公众号secret',
  `pay_body` varchar(255) DEFAULT NULL COMMENT '商品名称',
  `order_prefix` varchar(50) DEFAULT NULL COMMENT '订单前缀',
  `is_gift` int(11) DEFAULT '1' COMMENT '是否有礼物0否、1是',
  `is_sign` int(11) DEFAULT '1' COMMENT '是否有报名0否、1是',
  `is_coerce` int(11) DEFAULT '0' COMMENT '是否强关0否、1是',
  `activity_notice` varchar(255) DEFAULT NULL COMMENT '活动公告',
  `online_flag` int(11) DEFAULT NULL COMMENT '0下线、1在线',
  `apply_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '报名总数',
  `vote_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '投票总数',
  `gift_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '礼物抵票总数',
  `total_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '总票数',
  `visit_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '访问总数',
  `sort` int(10) unsigned NOT NULL DEFAULT '255' COMMENT '列表排序',
  `start_score` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '抽奖所需积分',
  `vote_score` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '投票赠送积分',
  `max_red_packet` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '单个拉票红包最大金额',
  `red_packet_rule_image` varchar(200) DEFAULT NULL COMMENT '红包规则图',
  `category_id` int(11) NOT NULL COMMENT '活动ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='活动';

-- 数据导出被取消选择。
-- 导出  表 vote_mp.wangluo_activity_category 结构
CREATE TABLE IF NOT EXISTS `wangluo_activity_category` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) NOT NULL COMMENT '分类名',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否显示(0:否 1:是)',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除(0:否 1:是)',
  `sort` int(11) NOT NULL COMMENT '排序',
  `create_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='活动分类';

-- 数据导出被取消选择。
-- 导出  表 vote_mp.wangluo_activity_rule 结构
CREATE TABLE IF NOT EXISTS `wangluo_activity_rule` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `rule_name` varchar(50) DEFAULT NULL COMMENT '规则名称',
  `is_delete` int(1) DEFAULT '0' COMMENT '规则是否呗删除0否、1是',
  `rule_type` int(10) DEFAULT NULL COMMENT '规则类型:1仅投一次、2仅投n次、3两次间隔、4每天N人',
  `vote_num` int(10) DEFAULT NULL COMMENT '投票规则对应数量',
  `user_num` int(10) DEFAULT NULL COMMENT '投票规则对应用户数量',
  `time_unit` int(10) DEFAULT NULL COMMENT '投票间隔时间单位1：分钟、2：小时、3：天',
  `time_interval` int(10) DEFAULT NULL COMMENT '投票间隔时间',
  `msg_success` varchar(255) DEFAULT NULL COMMENT '成功提示信息',
  `msg_fail` varchar(255) DEFAULT NULL COMMENT '失败提示信息',
  `irregularities` varchar(255) DEFAULT NULL COMMENT '违规提示',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='活动规则';

-- 数据导出被取消选择。
-- 导出  表 vote_mp.wangluo_activity_sign 结构
CREATE TABLE IF NOT EXISTS `wangluo_activity_sign` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '表ID',
  `create_time` int(11) unsigned DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) unsigned DEFAULT NULL COMMENT '更新时间',
  `is_delete` tinyint(1) unsigned DEFAULT '0' COMMENT '是否删除(0:否 1:是)',
  `appid` varchar(20) DEFAULT '0' COMMENT '来源appid(0为后台添加的报名)',
  `activity_id` int(11) DEFAULT NULL COMMENT '所属活动ID',
  `username` varchar(255) DEFAULT NULL COMMENT '报名名称',
  `sex` int(11) DEFAULT NULL COMMENT '性别（1.男2.女）',
  `mobile` varchar(11) DEFAULT NULL COMMENT '联系电话',
  `sign_openid` varchar(255) DEFAULT NULL COMMENT '报名用户openid',
  `sign_unit` varchar(255) DEFAULT NULL COMMENT '报名人所属机构',
  `sign_class` varchar(255) DEFAULT NULL COMMENT '报名人所属单位',
  `sign_code` int(11) DEFAULT NULL COMMENT '报名人员编号（可当排序使用）',
  `sign_image` varchar(255) DEFAULT NULL COMMENT '封面图',
  `sign_video` varchar(255) DEFAULT NULL COMMENT '参赛视频',
  `sign_audio` varchar(255) DEFAULT NULL COMMENT '参赛音频',
  `sign_duration` varchar(255) DEFAULT NULL COMMENT '音频长度',
  `sign_declaration` varchar(255) DEFAULT NULL COMMENT '参赛宣言',
  `sign_introduce_image` text COMMENT '风采介绍图',
  `sign_introduce` varchar(255) DEFAULT NULL COMMENT '风采介绍',
  `admin_count` int(11) DEFAULT '0' COMMENT '管理员调整票数',
  `vote_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户投票数',
  `gift_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户礼物票数',
  `total_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '总票数',
  `gift_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '礼物数量',
  `is_lock` int(11) DEFAULT '0' COMMENT '是否锁定0否、1是',
  `audit_flag` tinyint(1) DEFAULT NULL COMMENT '审核状态（1.审核通过2.待审核）',
  `red_packet` decimal(10,2) DEFAULT '0.00' COMMENT '拉票红包',
  `message_count` int(10) unsigned DEFAULT '0' COMMENT '留言总数',
  PRIMARY KEY (`id`),
  KEY `activity_id` (`activity_id`) USING BTREE,
  KEY `is_delete` (`is_delete`),
  KEY `audit_flag` (`audit_flag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='活动报名';

-- 数据导出被取消选择。
-- 导出  表 vote_mp.wangluo_activity_wechat 结构
CREATE TABLE IF NOT EXISTS `wangluo_activity_wechat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `create_time` int(11) unsigned NOT NULL COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL COMMENT '更新时间',
  `mp_id` int(11) unsigned NOT NULL COMMENT 'mp表ID',
  `activity_id` int(11) unsigned NOT NULL COMMENT '活动ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='小程序活动关联';

-- 数据导出被取消选择。
-- 导出  表 vote_mp.wangluo_ad 结构
CREATE TABLE IF NOT EXISTS `wangluo_ad` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `create_time` int(11) unsigned NOT NULL COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL COMMENT '更新时间',
  `appid` varchar(20) NOT NULL COMMENT '小程序APPID',
  `adp_id` int(11) NOT NULL COMMENT '广告位ID',
  `activity_id` varchar(255) DEFAULT NULL COMMENT '所属活动ID',
  `ad_name` varchar(255) DEFAULT NULL COMMENT '广告标题',
  `ad_image` varchar(255) DEFAULT NULL COMMENT '广告图片',
  `sort` int(11) unsigned NOT NULL DEFAULT '50' COMMENT '排序',
  `ad_linkcontent` longtext COMMENT '链接内容',
  `ad_introduce` varchar(255) NOT NULL COMMENT '广告介绍',
  `is_delete` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除(0:否 1:是)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='广告';

-- 数据导出被取消选择。
-- 导出  表 vote_mp.wangluo_admin_user 结构
CREATE TABLE IF NOT EXISTS `wangluo_admin_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `create_time` int(11) unsigned NOT NULL COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL COMMENT '更新时间',
  `user_name` varchar(20) NOT NULL COMMENT '用户名',
  `user_account` varchar(20) NOT NULL COMMENT '用户帐号',
  `user_password` char(40) NOT NULL COMMENT '用户密码（SHA1）',
  `user_email` varchar(200) NOT NULL COMMENT '邮箱',
  `role_id` int(10) unsigned NOT NULL COMMENT '角色ID',
  `is_delete` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除0否、1是',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理员用户';

-- 数据导出被取消选择。
-- 导出  表 vote_mp.wangluo_admin_user_log 结构
CREATE TABLE IF NOT EXISTS `wangluo_admin_user_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_account` varchar(255) DEFAULT NULL COMMENT '操作者人帐号',
  `action` varchar(255) DEFAULT NULL COMMENT '操作内容方法',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理员操作日志';

-- 数据导出被取消选择。
-- 导出  表 vote_mp.wangluo_ad_position 结构
CREATE TABLE IF NOT EXISTS `wangluo_ad_position` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '表id',
  `create_time` int(11) unsigned NOT NULL COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL COMMENT '更新时间',
  `adp_code` varchar(50) NOT NULL COMMENT '广告位标识符',
  `adp_name` varchar(50) DEFAULT NULL COMMENT '广告位名称',
  `adp_introduce` varchar(255) NOT NULL COMMENT '广告图片建议说明',
  `adp_width` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '广告位建议宽度',
  `adp_height` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '广告位建议高度',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除0否、1是',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='广告位';

-- 数据导出被取消选择。
-- 导出  表 vote_mp.wangluo_award 结构
CREATE TABLE IF NOT EXISTS `wangluo_award` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL COMMENT '奖项名',
  `location` int(10) unsigned NOT NULL COMMENT '奖项位置(角度)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='抽奖奖项';

-- 数据导出被取消选择。
-- 导出  表 vote_mp.wangluo_award_rule 结构
CREATE TABLE IF NOT EXISTS `wangluo_award_rule` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `activity_id` int(10) unsigned NOT NULL COMMENT '活动ID',
  `award_id` int(10) unsigned NOT NULL COMMENT '奖品ID',
  `odds` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '中奖几率百分比',
  `num` int(10) NOT NULL DEFAULT '0' COMMENT '奖品数量（-1为不限制）',
  PRIMARY KEY (`id`),
  KEY `activity_id` (`activity_id`),
  KEY `award_id` (`award_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='奖品规则';

-- 数据导出被取消选择。
-- 导出  表 vote_mp.wangluo_black_id 结构
CREATE TABLE IF NOT EXISTS `wangluo_black_id` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `create_time` int(11) DEFAULT NULL COMMENT '数据创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `sign_id` varchar(255) DEFAULT '' COMMENT '报名人ID',
  `activity_id` int(11) NOT NULL COMMENT '活动ID',
  `stoptime` int(11) DEFAULT NULL COMMENT '停止时间',
  `status` bigint(21) NOT NULL DEFAULT '0' COMMENT '是否处理的状态0否，1是',
  `vote_num` int(11) DEFAULT NULL COMMENT '该监测时间段的票数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='黑名单';

-- 数据导出被取消选择。
-- 导出  表 vote_mp.wangluo_gift 结构
CREATE TABLE IF NOT EXISTS `wangluo_gift` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `create_time` int(11) unsigned DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) unsigned DEFAULT NULL COMMENT '更新时间',
  `gift_name` varchar(999) DEFAULT NULL COMMENT '礼物名称',
  `gift_value` decimal(10,2) DEFAULT NULL COMMENT '价值(单位:元)',
  `gift_image` varchar(255) DEFAULT NULL COMMENT '礼物图片',
  `vote_num` int(11) DEFAULT NULL COMMENT '可兑换票数',
  `sort` int(10) unsigned NOT NULL DEFAULT '50' COMMENT '礼物排序',
  `is_delete` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除(0:否 1:是)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='礼物';

-- 数据导出被取消选择。
-- 导出  表 vote_mp.wangluo_guestbook 结构
CREATE TABLE IF NOT EXISTS `wangluo_guestbook` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_id` int(10) unsigned NOT NULL COMMENT '活动ID',
  `sign_id` int(10) unsigned NOT NULL COMMENT '报名ID',
  `openid` varchar(50) NOT NULL COMMENT '用户OPENID',
  `content` text NOT NULL COMMENT '留言内容',
  `create_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL COMMENT '修改时间',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除(0:否 1:是)',
  PRIMARY KEY (`id`),
  KEY `activity_id` (`activity_id`),
  KEY `sign_id` (`sign_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='留言';

-- 数据导出被取消选择。
-- 导出  表 vote_mp.wangluo_mp 结构
CREATE TABLE IF NOT EXISTS `wangluo_mp` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int(10) unsigned NOT NULL COMMENT '绑定的用户ID',
  `appid` char(18) NOT NULL COMMENT '小程序APPID',
  `access_token` varchar(200) DEFAULT NULL COMMENT 'access_token',
  `access_token_create_time` int(10) unsigned DEFAULT NULL COMMENT 'access_token创建时间',
  `access_token_expire` int(10) unsigned DEFAULT NULL COMMENT 'access_token有效期',
  `refresh_token` varchar(200) DEFAULT NULL COMMENT 'refresh_token',
  `mp_name` text NOT NULL COMMENT '小程序名称',
  `create_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL COMMENT '修改时间',
  `is_delete` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除(0:否 1:是)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='授权小程序';

-- 数据导出被取消选择。
-- 导出  表 vote_mp.wangluo_node 结构
CREATE TABLE IF NOT EXISTS `wangluo_node` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned NOT NULL DEFAULT '0',
  `node_title` varchar(20) NOT NULL COMMENT '节点标题',
  `node_name` varchar(20) NOT NULL COMMENT '节点名称',
  `sort` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `is_delete` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  `create_time` int(11) unsigned NOT NULL COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='节点';

-- 数据导出被取消选择。
-- 导出  表 vote_mp.wangluo_order 结构
CREATE TABLE IF NOT EXISTS `wangluo_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `activity_type` tinyint(1) NOT NULL COMMENT '活动类型(1个人/2商家/3景物)',
  `appid` varchar(20) NOT NULL COMMENT '小程序APPID',
  `activity_id` int(11) DEFAULT NULL COMMENT '活动id',
  `openid` varchar(255) NOT NULL COMMENT '用户唯一标识',
  `gift_id` int(11) DEFAULT NULL COMMENT '礼物id',
  `gift_name` varchar(50) NOT NULL COMMENT '礼物名',
  `sign_id` int(11) DEFAULT NULL COMMENT '报名人id',
  `gift_num` int(11) DEFAULT NULL COMMENT '礼物个数',
  `total_amount` decimal(10,2) unsigned NOT NULL COMMENT '总价',
  `order_sn` varchar(255) NOT NULL COMMENT '订单编号',
  `order_status` int(11) DEFAULT '1' COMMENT '订单状态(1.未支付2.已支付)',
  `transaction_id` varchar(255) DEFAULT NULL COMMENT '流水号',
  `name` varchar(255) DEFAULT NULL COMMENT '昵称',
  `headimgurl` varchar(255) DEFAULT NULL COMMENT '头像',
  PRIMARY KEY (`id`),
  KEY `activity_id` (`activity_id`),
  KEY `sign_id` (`sign_id`),
  KEY `order_status` (`order_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='订单';

-- 数据导出被取消选择。
-- 导出  表 vote_mp.wangluo_red_packet 结构
CREATE TABLE IF NOT EXISTS `wangluo_red_packet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_id` int(10) unsigned NOT NULL COMMENT '活动ID',
  `sign_id` int(10) unsigned NOT NULL COMMENT '报名ID',
  `openid` varchar(50) NOT NULL COMMENT '用户OPENID',
  `amount` decimal(10,2) NOT NULL COMMENT '红包金额',
  `order_sn` varchar(100) NOT NULL COMMENT '订单号',
  `order_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态(0:未支付 1:已支付)',
  `transaction_id` varchar(200) DEFAULT NULL COMMENT '流水号',
  `create_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `activity_id` (`activity_id`),
  KEY `sign_id` (`sign_id`),
  KEY `order_status` (`order_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='报名拉票红包';

-- 数据导出被取消选择。
-- 导出  表 vote_mp.wangluo_red_packet_cash 结构
CREATE TABLE IF NOT EXISTS `wangluo_red_packet_cash` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `openid` varchar(50) NOT NULL COMMENT '用户OPENID',
  `amount` decimal(10,2) NOT NULL COMMENT '金额',
  `order_sn` varchar(200) NOT NULL COMMENT '订单号',
  `create_time` int(11) NOT NULL COMMENT '时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='红包提现';

-- 数据导出被取消选择。
-- 导出  表 vote_mp.wangluo_role 结构
CREATE TABLE IF NOT EXISTS `wangluo_role` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `role_name` varchar(20) NOT NULL COMMENT '角色名',
  `sort` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `is_delete` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  `create_time` int(11) unsigned NOT NULL COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='角色';

-- 数据导出被取消选择。
-- 导出  表 vote_mp.wangluo_score_rule 结构
CREATE TABLE IF NOT EXISTS `wangluo_score_rule` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `activity_id` int(10) unsigned NOT NULL COMMENT '活动ID',
  `gift_id` int(10) unsigned NOT NULL COMMENT '礼物ID',
  `score` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '赠送积分数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='积分规则';

-- 数据导出被取消选择。
-- 导出  表 vote_mp.wangluo_user 结构
CREATE TABLE IF NOT EXISTS `wangluo_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_name` varchar(20) NOT NULL COMMENT '用户名',
  `user_account` varchar(20) NOT NULL COMMENT '用户帐号',
  `user_password` char(40) NOT NULL COMMENT '用户密码（SHA1）',
  `role_id` int(10) unsigned NOT NULL COMMENT '角色ID',
  `is_delete` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '删除',
  `create_time` int(11) unsigned NOT NULL COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='前台用户';

-- 数据导出被取消选择。
-- 导出  表 vote_mp.wangluo_user_award 结构
CREATE TABLE IF NOT EXISTS `wangluo_user_award` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `openid` varchar(50) NOT NULL COMMENT '用户OPENID',
  `activity_id` int(10) unsigned NOT NULL COMMENT '活动ID',
  `award_id` int(10) unsigned NOT NULL COMMENT '奖品ID',
  `create_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  `confirm_time` int(10) unsigned DEFAULT NULL COMMENT '确认时间',
  PRIMARY KEY (`id`),
  KEY `activity_id` (`activity_id`),
  KEY `award_id` (`award_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户奖品';

-- 数据导出被取消选择。
-- 导出  表 vote_mp.wangluo_user_red_packet 结构
CREATE TABLE IF NOT EXISTS `wangluo_user_red_packet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_id` int(10) unsigned NOT NULL COMMENT '活动ID',
  `sign_id` int(10) unsigned NOT NULL COMMENT '报名ID',
  `openid` varchar(50) NOT NULL COMMENT '用户OPENID',
  `action` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '行为(1:投票 2:礼物)',
  `gift_id` int(10) unsigned DEFAULT NULL COMMENT '礼物ID',
  `amount` decimal(10,2) NOT NULL COMMENT '红包金额',
  `create_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `activity_id` (`activity_id`),
  KEY `sign_id` (`sign_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户红包';

-- 数据导出被取消选择。
-- 导出  表 vote_mp.wangluo_user_score 结构
CREATE TABLE IF NOT EXISTS `wangluo_user_score` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `openid` varchar(50) NOT NULL COMMENT '用户OPENID',
  `activity_id` int(10) unsigned NOT NULL COMMENT '活动ID',
  `score` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户积分',
  `create_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `activity_id` (`activity_id`),
  KEY `openid` (`openid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户积分';

-- 数据导出被取消选择。
-- 导出  表 vote_mp.wangluo_vote 结构
CREATE TABLE IF NOT EXISTS `wangluo_vote` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `create_time` int(11) unsigned NOT NULL COMMENT '创建时间',
  `sign_id` int(11) NOT NULL COMMENT '报名用户ID',
  `appid` varchar(20) NOT NULL COMMENT '来源appid',
  `activity_id` int(11) NOT NULL COMMENT '活动ID',
  `voter_openid` varchar(255) NOT NULL COMMENT '投票人的openid',
  `voter_ip` varchar(255) DEFAULT NULL COMMENT '投票ip',
  PRIMARY KEY (`id`),
  KEY `vote_index_seller` (`sign_id`) USING BTREE,
  KEY `vote_index_activity_id` (`activity_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='投票';

-- 数据导出被取消选择。
-- 导出  表 vote_mp.wangluo_vote_log 结构
CREATE TABLE IF NOT EXISTS `wangluo_vote_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `appid` char(18) NOT NULL COMMENT '小程序APPID',
  `activity_id` int(10) unsigned NOT NULL COMMENT '活动ID',
  `openid` varchar(50) NOT NULL COMMENT 'openid',
  `vote_date` date NOT NULL COMMENT '投票日期',
  `vote_time` int(10) unsigned NOT NULL COMMENT '最后投票时间',
  `vote_num` int(10) unsigned NOT NULL COMMENT '投票数',
  `vote_sign_num` int(10) unsigned NOT NULL COMMENT '投票人数',
  `vote_sign_ids` text COMMENT '投票人ID列表(逗号分隔)',
  PRIMARY KEY (`id`),
  KEY `appid` (`appid`),
  KEY `activity_id` (`activity_id`),
  KEY `open_id` (`openid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户投票日志';

-- 数据导出被取消选择。
-- 导出  表 vote_mp.wangluo_vote_maker 结构
CREATE TABLE IF NOT EXISTS `wangluo_vote_maker` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `sign_id` int(10) unsigned NOT NULL COMMENT '报名ID',
  `vote_count` int(10) unsigned NOT NULL COMMENT '增加总票数',
  `vote_current` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '已增加票数',
  `is_finish` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0:否 1:是',
  `start_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
  `stop_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  `vote_per_sec` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '平均每秒增加票数',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `is_delete` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除 0:否 1:是',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='自动刷票器';

-- 数据导出被取消选择。
-- 导出  表 vote_mp.wangluo_vote_warnning 结构
CREATE TABLE IF NOT EXISTS `wangluo_vote_warnning` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `create_time` int(11) unsigned NOT NULL COMMENT '创建时间',
  `vote_id` int(11) NOT NULL COMMENT '投票表ID',
  `vote_create_time` int(11) DEFAULT NULL COMMENT '投票表的创建时间',
  `sign_id` int(11) NOT NULL COMMENT '报名用户ID',
  `appid` varchar(20) NOT NULL COMMENT '来源appid',
  `activity_id` int(11) NOT NULL COMMENT '活动ID',
  `voter_openid` varchar(255) NOT NULL COMMENT '投票人的openid',
  `voter_ip` varchar(255) DEFAULT NULL COMMENT '投票ip',
  `voter_province` varchar(255) DEFAULT NULL COMMENT '投票IP所在省',
  `voter_city` varchar(255) DEFAULT NULL COMMENT '投票IP所在市',
  `voter_county` varchar(255) DEFAULT NULL COMMENT '投票IP所在区',
  PRIMARY KEY (`id`),
  KEY `vote_index_seller` (`sign_id`) USING BTREE,
  KEY `vote_index_activity_id` (`activity_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='投票异常信息报警表';

-- 数据导出被取消选择。
-- 导出  表 vote_mp.wangluo_wx_user 结构
CREATE TABLE IF NOT EXISTS `wangluo_wx_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `appid` varchar(50) NOT NULL COMMENT 'APPID',
  `openid` varchar(50) NOT NULL COMMENT 'openid',
  `session_key` varchar(50) NOT NULL COMMENT 'session_key',
  `session_id` varchar(50) NOT NULL COMMENT 'session_id',
  `nick_name` varchar(50) DEFAULT '匿名用户' COMMENT '昵称',
  `avatar_url` varchar(200) DEFAULT 'https://mp.drinkwall.cn/avatar.jpg' COMMENT '头像',
  `gender` tinyint(1) DEFAULT NULL COMMENT '1:男 2:女 3:未知',
  `city` varchar(20) DEFAULT NULL COMMENT '城市',
  `province` varchar(20) DEFAULT NULL COMMENT '省份',
  `country` varchar(20) DEFAULT NULL COMMENT '国家',
  `language` varchar(10) DEFAULT NULL COMMENT '语言',
  `create_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL COMMENT '更新时间',
  `is_developer` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '开发人员(支付均为1分)',
  `red_packet` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '红包',
  `address` varchar(200) DEFAULT NULL COMMENT '收货地址',
  PRIMARY KEY (`id`),
  KEY `openid` (`openid`) USING BTREE,
  KEY `session_id` (`session_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='微信用户';

-- 数据导出被取消选择。
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
