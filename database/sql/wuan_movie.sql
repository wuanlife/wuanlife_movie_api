-- 用户信息表
CREATE TABLE IF NOT EXISTS users_base
(
  id        INT UNSIGNED AUTO_INCREMENT  NOT NULL
  COMMENT '用户id',
  mail      VARCHAR(30) COLLATE utf8_bin NOT NULL
  COMMENT '用户邮箱',
  name      CHAR(20) COLLATE utf8_bin    NOT NULL
  COMMENT '用户名',
  password  CHAR(32) COLLATE utf8_bin    NOT NULL
  COMMENT '用户密码',
  create_at TIMESTAMP                    NOT NULL
  COMMENT '注册时间',
  PRIMARY KEY (id),
  KEY login_index(mail, password)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT ='用户基础信息表';

-- 用户头像表
CREATE TABLE IF NOT EXISTS avatar_url
(
  user_id    INT UNSIGNED AUTO_INCREMENT   NOT NULL
  COMMENT '用户id',
  url        VARCHAR(255) COLLATE utf8_bin NOT NULL
  COMMENT '图片url',
  delete_flg TINYINT UNSIGNED              NOT NULL DEFAULT 0
  COMMENT '图片是否已删除',
  KEY image_user_id(user_id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT ='用户头像url表';

-- 用户权限表
-- 用于实现管理员功能，
-- 因为取消了group功能，所以身份关系是唯一的，但是考虑到日后的可扩展性，
-- 此处使用 | 操作符来进行状态的叠加，使用 & 运算符进行状态的判断
CREATE TABLE IF NOT EXISTS users_auth
(
  id   INT UNSIGNED              NOT NULL
  COMMENT '用户id',
  name CHAR(20) COLLATE utf8_bin NOT NULL
  COMMENT '用户名',
  auth INT UNSIGNED              NOT NULL
  COMMENT '用户权限',
  PRIMARY KEY (id),
  KEY auth(auth)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT ='用户权限表';

-- 权限对应关系表
-- 使用 << 运算符来得到不同的状态码
CREATE TABLE IF NOT EXISTS auth_detail
(
  id        INT UNSIGNED AUTO_INCREMENT  NOT NULL
  COMMENT '权限id(偏移量)',
  indentity VARCHAR(30) COLLATE utf8_bin NOT NULL
  COMMENT '权限类型',
  PRIMARY KEY (id),
  UNIQUE KEY (indentity)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT ='权限对应关系表';

-- 影片基础信息表
CREATE TABLE IF NOT EXISTS movies_base
(
  `id`     INT UNSIGNED AUTO_INCREMENT   NOT NULL
  COMMENT '影片id',
  `title`  VARCHAR(50)  COLLATE utf8_bin NOT NULL
  COMMENT '影片标题',
  `digest` VARCHAR(255) COLLATE utf8_bin NOT NULL
  COMMENT '摘要',
  PRIMARY KEY (id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT ='影片基础信息表';

-- 影片分类表
CREATE TABLE IF NOT EXISTS movies_sort
(
  `movies_id` INT UNSIGNED     NOT NULL
  COMMENT '影片id',
  `sort_id`   TINYINT UNSIGNED NOT NULL
  COMMENT '分类id',
  PRIMARY KEY (movies_id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT ='影片分类表';

-- 影片详情表
CREATE TABLE IF NOT EXISTS movies_details
(
  `id`             INT UNSIGNED                  NOT NULL
  COMMENT '影片id',
  `original_title` VARCHAR(50) COLLATE utf8_bin  NOT NULL
  COMMENT '影片原名',
  `countries`      VARCHAR(15) COLLATE utf8_bin  NOT NULL
  COMMENT '制片国家/地区',
  `year`           CHAR(4) COLLATE utf8_bin      NOT NULL
  COMMENT '年代',
  `genres`         VARCHAR(25) COLLATE utf8_bin  NOT NULL
  COMMENT '影片类型',
  `aka`            VARCHAR(255) COLLATE utf8_bin NOT NULL
  COMMENT '影片别名',
  url_douban       VARCHAR(255) COLLATE utf8_bin NOT NULL
  COMMENT '豆瓣链接',
  PRIMARY KEY (id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT ='影片详情表';

-- 剧情简介表
CREATE TABLE IF NOT EXISTS movies_summary
(
  `id`      INT UNSIGNED                  NOT NULL
  COMMENT '影片id',
  `summary` VARCHAR(500) COLLATE utf8_bin NOT NULL
  COMMENT '影片简介',
  PRIMARY KEY (id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT ='剧情简介表';

-- 影片海报表
CREATE TABLE IF NOT EXISTS movies_poster
(
  `id`  INT UNSIGNED NOT NULL
  COMMENT '影片id',
  `url` VARCHAR(255) COLLATE utf8_bin COMMENT '海报链接',
  PRIMARY KEY (id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT ='影片海报表';

-- 影片评分表
CREATE TABLE IF NOT EXISTS movies_rating
(
  `id`     INT UNSIGNED         NOT NULL
  COMMENT '影片id',
  `rating` FLOAT(2, 1) UNSIGNED NOT NULL
  COMMENT '影片评分',
  PRIMARY KEY (id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT ='影片评分表';

-- 演员表
CREATE TABLE IF NOT EXISTS movies_actors
(
  `id`   INT UNSIGNED NOT NULL
  COMMENT '演员id',
  `name` VARCHAR(30) COLLATE utf8_bin COMMENT '演员名',
  PRIMARY KEY (id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT ='演员表';

-- 导演表
CREATE TABLE IF NOT EXISTS movies_director
(
  `id`   INT UNSIGNED NOT NULL
  COMMENT '导演id',
  `name` VARCHAR(30) COLLATE utf8_bin COMMENT '导演名',
  PRIMARY KEY (id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT ='导演表';

-- 影片演员表
CREATE TABLE IF NOT EXISTS movies_actors
(
  `movies_id` INT UNSIGNED NOT NULL
  COMMENT '影片id',
  `actors_id` INT UNSIGNED NOT NULL
  COMMENT '演员id',
  PRIMARY KEY (movies_id, actors_id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT ='影片演员表';

-- 影片导演表
CREATE TABLE IF NOT EXISTS movies_directors
(
  `movies_id`    INT UNSIGNED NOT NULL
  COMMENT '影片id',
  `directors_id` INT UNSIGNED NOT NULL
  COMMENT '导演id',
  PRIMARY KEY (movies_id, directors_id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT ='影片导演表';

-- 资源表
CREATE TABLE IF NOT EXISTS resource
(
  `movies_id`     INT UNSIGNED                  NOT NULL
  COMMENT '影片id',
  `resource_id`   INT UNSIGNED                  NOT NULL
  COMMENT '资源id',
  `resource_sort` TINYINT UNSIGNED              NOT NULL
  COMMENT '资源种类',
  `title`         VARCHAR(50)  COLLATE utf8_bin NOT NULL
  COMMENT '资源标题',
  `instruction`   VARCHAR(255) COLLATE utf8_bin NOT NULL
  COMMENT '资源描述',
  `sharer`        CHAR(20) COLLATE utf8_bin     NOT NULL
  COMMENT '分享者id',
  `url`           VARCHAR(255) COLLATE utf8_bin NOT NULL
  COMMENT '资源链接',
  `password`      CHAR(8) COLLATE utf8_bin      NULL
  COMMENT '资源密码(网盘)',
  `create_at`     TIMESTAMP                     NOT NULL
  COMMENT '资源发布时间',
  PRIMARY KEY (movies_id, resource_id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT ='资源表';

-- 资源种类字典表
CREATE TABLE IF NOT EXISTS resource_sort_details
(
  `sort_id`   TINYINT UNSIGNED          NOT NULL
  COMMENT '资源种类id',
  `sort_name` CHAR(10) COLLATE utf8_bin NOT NULL
  COMMENT '资源种类名',
  PRIMARY KEY (sort_id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT ='资源种类字典表';

-- 分类字典表
CREATE TABLE IF NOT EXISTS movies_sort_details
(
  `sort_id`   TINYINT UNSIGNED          NOT NULL
  COMMENT '分类id',
  `sort_name` CHAR(10) COLLATE utf8_bin NOT NULL
  COMMENT '分类名',
  PRIMARY KEY (sort_id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT ='分类字典表';