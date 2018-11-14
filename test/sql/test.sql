
-- ----------------------------
-- Table structure for cf_class
-- ----------------------------
DROP TABLE IF EXISTS `cf_class`;
CREATE TABLE `cf_class` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `name` varchar(50) NOT NULL COMMENT '班级',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='班级表';

-- ----------------------------
-- Records of cf_class
-- ----------------------------
INSERT INTO `cf_class` VALUES ('1', '一年级');
INSERT INTO `cf_class` VALUES ('2', '二年级');
INSERT INTO `cf_class` VALUES ('3', '三年级');

-- ----------------------------
-- Table structure for cf_stu
-- ----------------------------
DROP TABLE IF EXISTS `cf_stu`;
CREATE TABLE `cf_stu` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `class_id` int(11) NOT NULL COMMENT '班级ID',
  `name` varchar(50) NOT NULL COMMENT '姓名',
  `sex` varchar(50) NOT NULL COMMENT '性别',
  `is_master` tinyint(1) NOT NULL DEFAULT 0 COMMENT '班长',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='学生表';

-- ----------------------------
-- Records of cf_stu
-- ----------------------------
INSERT INTO `cf_stu` VALUES ('1', '1', '姓名1_1', '男', 1);
INSERT INTO `cf_stu` VALUES ('2', '2', '姓名2_1', '男', 0);
INSERT INTO `cf_stu` VALUES ('3', '2', '姓名2_2', '男', 1);
INSERT INTO `cf_stu` VALUES ('4', '3', '姓名3_1', '男', 0);
INSERT INTO `cf_stu` VALUES ('5', '3', '姓名3_2', '男', 0);
INSERT INTO `cf_stu` VALUES ('6', '3', '姓名3_3', '男', 1);

-- ----------------------------
-- Table structure for cf_stu_course
-- ----------------------------
DROP TABLE IF EXISTS `cf_stu_course`;
CREATE TABLE `cf_stu_course` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `stu_id` int(11) NOT NULL COMMENT '学生ID',
  `name` varchar(50) NOT NULL COMMENT '课程名称',
  PRIMARY KEY (`id`),
  KEY `stu_id` (`stu_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COMMENT='学生课程表';

-- ----------------------------
-- Records of cf_stu_course
-- ----------------------------
INSERT INTO `cf_stu_course` VALUES ('1', '1', '英语');
INSERT INTO `cf_stu_course` VALUES ('2', '2', '英语');
INSERT INTO `cf_stu_course` VALUES ('3', '2', '语文');
INSERT INTO `cf_stu_course` VALUES ('4', '3', '英语');
INSERT INTO `cf_stu_course` VALUES ('5', '3', '语文');
INSERT INTO `cf_stu_course` VALUES ('6', '3', '数学');
INSERT INTO `cf_stu_course` VALUES ('7', '4', '英语');
INSERT INTO `cf_stu_course` VALUES ('8', '4', '语文');
INSERT INTO `cf_stu_course` VALUES ('9', '4', '数学');
INSERT INTO `cf_stu_course` VALUES ('10', '4', '历史');