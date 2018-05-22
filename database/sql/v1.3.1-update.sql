-- 积分表
CREATE TABLE IF NOT EXISTS points
(
  user_id INT UNSIGNED NOT NULL
  COMMENT '用户id',
  scores  INT UNSIGNED NOT NULL
  COMMENT '午安影视积分',
  PRIMARY KEY (user_id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT = '用户积分表';

-- 待审核资源表a
CREATE TABLE IF NOT EXISTS unreviewed_resources
(
  resource_id INT UNSIGNED NOT NULL
  COMMENT '资源id',
  PRIMARY KEY (resource_id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT = '待审核资源表';

-- 积分兑换记录表
CREATE TABLE IF NOT EXISTS points_order
(
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL
  COMMENT '用户id',
  points_alert  INT NOT NULL
  COMMENT '午安影视积分',
  created_at TIMESTAMP,
  PRIMARY KEY (id)
)
