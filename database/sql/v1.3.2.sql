-- 用户权限表
CREATE TABLE IF NOT EXISTS users_auth
(
  id   INT UNSIGNED NOT NULL
  COMMENT '用户id',
  auth INT UNSIGNED NOT NULL
  COMMENT '用户权限',
  PRIMARY KEY (id, auth)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT ='用户权限表';

-- 权限对应关系表
CREATE TABLE IF NOT EXISTS auth_detail
(
  id        INT UNSIGNED AUTO_INCREMENT  NOT NULL
  COMMENT '权限id(偏移量)',
  indentity VARCHAR(30) COLLATE utf8_bin NOT NULL
  COMMENT '权限类型',
  PRIMARY KEY (id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT ='权限对应关系表';

-- 影片基础信息表
CREATE TABLE IF NOT EXISTS movies_base
(
  `id`         INT UNSIGNED                  NOT NULL
  COMMENT '影片id',
  `type`       TINYINT UNSIGNED              NOT NULL
  COMMENT '影片分类(影片首页分类)',
  `title`      VARCHAR(50)  COLLATE utf8_bin NOT NULL
  COMMENT '影片标题',
  `digest`     VARCHAR(255) COLLATE utf8_bin NOT NULL
  COMMENT '摘要',
  `created_at` TIMESTAMP                     NOT NULL
  COMMENT '资源添加时间',
  PRIMARY KEY (id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT ='影片基础信息表';

-- 影片详情表
CREATE TABLE IF NOT EXISTS movies_details
(
  `id`             INT UNSIGNED                  NOT NULL
  COMMENT '影片id',
  `title`          VARCHAR(50)  COLLATE utf8_bin NOT NULL
  COMMENT '影片标题',
  `original_title` VARCHAR(50) COLLATE utf8_bin  NOT NULL
  COMMENT '影片原名',
  -- `genres`         VARCHAR(255) COLLATE utf8_bin NOT NULL
  -- COMMENT '影片类型(影片详情页显示)',
  `countries`      VARCHAR(15) COLLATE utf8_bin  NOT NULL
  COMMENT '制片国家/地区',
  `year`           CHAR(4) COLLATE utf8_bin      NOT NULL
  COMMENT '年代',
  `aka`            VARCHAR(255) COLLATE utf8_bin NOT NULL
  COMMENT '影片别名',
  `url_douban`     VARCHAR(255) COLLATE utf8_bin NOT NULL
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
CREATE TABLE IF NOT EXISTS actors
(
  `id`   INT UNSIGNED NOT NULL AUTO_INCREMENT
  COMMENT '演员id',
  `name` VARCHAR(30) COLLATE utf8_bin COMMENT '演员名',
  PRIMARY KEY (id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT ='演员表';

-- 导演表
CREATE TABLE IF NOT EXISTS directors
(
  `id`   INT UNSIGNED NOT NULL AUTO_INCREMENT
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
  `movie_id` INT UNSIGNED NOT NULL
  COMMENT '影片id',
  `actor_id` INT UNSIGNED NOT NULL
  COMMENT '演员id',
  PRIMARY KEY (movie_id, actor_id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT ='影片演员表';

-- 影片导演表
CREATE TABLE IF NOT EXISTS movies_directors
(
  `movie_id`    INT UNSIGNED NOT NULL
  COMMENT '影片id',
  `director_id` INT UNSIGNED NOT NULL
  COMMENT '导演id',
  PRIMARY KEY (movie_id, director_id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT ='影片导演表';

-- 资源表
CREATE TABLE IF NOT EXISTS resources
(
  `movies_id`     INT UNSIGNED                   NOT NULL
  COMMENT '影片id',
  `resource_id`   INT UNSIGNED AUTO_INCREMENT    NOT NULL
  COMMENT '资源id',
  `resource_type` TINYINT UNSIGNED               NOT NULL
  COMMENT '资源种类id',
  `title`         VARCHAR(50)  COLLATE utf8_bin  NOT NULL
  COMMENT '资源标题',
  `instruction`   VARCHAR(255) COLLATE utf8_bin  NOT NULL
  COMMENT '资源描述',
  `sharer`        INT UNSIGNED                   NOT NULL
  COMMENT '分享者id',
  `url`           VARCHAR(1000) COLLATE utf8_bin NOT NULL
  COMMENT '资源链接',
  `password`      CHAR(8) COLLATE utf8_bin       NULL
  COMMENT '资源密码(网盘)',
  `updated_at`    TIMESTAMP                      NOT NULL
  COMMENT '资源更新时间',
  `created_at`    TIMESTAMP                      NOT NULL
  COMMENT '资源发布时间',
  PRIMARY KEY (resource_id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT ='资源表';

-- 资源种类字典表
CREATE TABLE IF NOT EXISTS resources_type_details
(
  `type_id`   TINYINT UNSIGNED             NOT NULL
  COMMENT '资源种类id',
  `type_name` VARCHAR(10) COLLATE utf8_bin NOT NULL
  COMMENT '资源种类名',
  PRIMARY KEY (type_id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT ='资源种类字典表';

-- 影片类型表(影片详情页面显示)
CREATE TABLE IF NOT EXISTS movies_genres
(
  `movies_id` INT UNSIGNED     NOT NULL
  COMMENT '影片id',
  `genres_id` TINYINT UNSIGNED NOT NULL
  COMMENT '影片类型id',
  PRIMARY KEY (movies_id, genres_id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT ='影片类型表(影片详情页面显示)';

-- 类型字典表(影片详情页显示)
CREATE TABLE IF NOT EXISTS movies_genres_details
(
  `genres_id`   TINYINT UNSIGNED             NOT NULL AUTO_INCREMENT
  COMMENT '类型id',
  `genres_name` VARCHAR(10) COLLATE utf8_bin NOT NULL
  COMMENT '类型名',
  PRIMARY KEY (genres_id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT ='影片类型表(影片详情页面显示)';

-- 影片分类表(首页分类栏)
CREATE TABLE IF NOT EXISTS movies_type
(
  `movies_id` INT UNSIGNED     NOT NULL
  COMMENT '影片id',
  `type_id`   TINYINT UNSIGNED NOT NULL
  COMMENT '分类id',
  PRIMARY KEY (movies_id, type_id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT ='影片分类表';

-- 分类字典表(首页分类栏)
CREATE TABLE IF NOT EXISTS movies_type_details
(
  `type_id`   TINYINT UNSIGNED             NOT NULL
  COMMENT '分类id',
  `type_name` VARCHAR(10) COLLATE utf8_bin NOT NULL
  COMMENT '分类名',
  PRIMARY KEY (type_id)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_bin
  COMMENT ='分类字典表';

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

