-- 设置管理员分类
INSERT INTO auth_detail VALUES (1,'管理员'),(2,'最高管理员');

-- 设置最高管理员
INSERT INTO `users_auth`(`id`, `auth`) VALUES (用户id, 最高权限id);