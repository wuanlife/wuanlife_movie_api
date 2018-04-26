-- 积分表
CREATE TABLE IF NOT EXISTS scores
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

-- 待审核资源表
CREATE TABLE IF NOT EXISTS unreviewed_resources
(
  resources_id INT UNSIGNED NOT NULL
  COMMENT '资源id',
  PRIMARY KEY (resources_id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT = '待审核资源表';

