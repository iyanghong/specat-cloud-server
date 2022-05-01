-- 用户表
create table if not exists users(
    user_id int primary key AUTO_INCREMENT COMMENT '用户编号',
    user_uuid char(32) not null comment '唯一标识',
    user_pwd varchar (100) not null comment '登录密码',
    user_name varchar(20) not null COMMENT '用户昵称',
    user_phone char (11) null comment '手机号码',
    user_email varchar (30) null comment '邮箱号码',
    user_sex tinyint null default 0 comment '用户性别',
    user_birthday date null COMMENT '用户生日',
    user_header varchar (200) not null comment '用户头像',
    user_info varchar(200) null comment '个性签名',
    user_address int null default -1 comment '用户地址',
    score bigint not null default 0 comment '积分',
    user_status tinyint not null default 1 comment '用户状态,0:失效 1:正常 2:冻结 3:违规 4:注销中',
    fans bigint not null default 0 comment '粉丝数',
    concern bigint not null default 0 comment '关注数',
    error_num int not null default 0 comment '密码错误次数',
    status_uuid varchar(32) null comment '状态编号',
    qq int null comment 'QQ号',
    wechat varchar(30) null comment '微信号',
    weibo varchar(30) null comment '微博号',
    user_ip varchar (20) not null comment '用户IP',
    login_expire int not null default 10800 comment '登陆过期时间',
    login_time datetime null comment '登录时间',
    created_at datetime null comment '添加时间',
    updated_at datetime null comment '修改时间',
    create_source varchar(20) not null default '手动注册' comment '注册来源',
    update_user char(32) null comment '修改人'
) ENGINE=INNODB AUTO_INCREMENT=10001 comment='用户表';


-- 用户角色
create table if not exists user_role(
    id int primary key auto_increment comment '编号',
    uuid char(32) not null unique comment '唯一标识',
    user_uuid char(32) not null comment '用户编号', -- exists: "users,user_uuid"
    role_uuid char(32) not null comment '角色编号', -- exists: "role,role_uuid"
    auth_time datetime null comment '添加时间'
) ENGINE=INNODB comment='用户角色';

-- 角色表
create table if not exists role(
    role_id int primary key auto_increment comment '角色编号',
    role_uuid char (32) not null comment '唯一标识',
    role_name varchar(30) not null comment '角色名称',
    role_info varchar(200) not null comment '角色简介',
    created_at datetime not null comment '添加时间',
    updated_at datetime null comment '修改时间',
    create_user char(32) not null comment '创建人',
    update_user char(32) null comment '修改人'
) ENGINE=INNODB comment='角色表';

-- 规则组表
create table if not exists rule_group(
    rule_group_id int primary key auto_increment comment '规则组编号',
    rule_group_uuid char (32) not null comment '唯一标识',
    parent_uuid char (32) null comment '父级UUID',
    rule_group_name varchar(30) not null comment '规则组名称',
    rule_group_info varchar(200) not null comment '规则组简介',
    created_at datetime not null comment '添加时间',
    updated_at datetime null comment '修改时间',
    create_user char(32) not null comment '创建人',
    update_user char(32) null comment '修改人'
) ENGINE=INNODB comment='规则组';

-- 规则表
create table if not exists rule(
    rule_id int primary key auto_increment comment '规则编号',
    rule_uuid char (32) not null unique comment '唯一标识',
    rule_name varchar(30) not null comment '规则名',
    rule_info varchar(200) not null comment '规则介绍',
    rule_code varchar(200) not null comment '规则代码',
    rule_group_uuid char(32) not null comment '规则组标识', -- exists: "rule_group,rule_group_uuid"
    type int not null default 1 comment '限制类型,1:接口 2:页面',
    rule_io varchar(30) default 'Global' comment '限制端',
    created_at datetime not null comment '添加时间',
    updated_at datetime null comment '修改时间',
    create_user char (32) not null comment '创建人',
    update_user char (32) null comment '修改人'
    ) ENGINE=INNODB comment='规则';

-- 权限表 角色-规则关系表
create table if not exists permission(
    permission_id int primary key auto_increment comment '编号',
    permission_uuid char(32) not null comment '唯一标识',
    role_uuid char(32) not null comment '角色', -- exists: "role,role_uuid"
    rule_uuid char(32) not null comment '规则',  -- exists: "rule,rule_uuid"
    auth_time datetime not null comment '授权时间'
)ENGINE=INNODB comment='权限表';

-- 导航菜单
create table if not exists left_menu(
    menu_id int primary key auto_increment comment '编号',
    menu_uuid char(32) not null comment '唯一标识',
    menu_name varchar(50) not null comment '菜单名称',
    menu_code varchar (100) null comment '菜单Code',
    menu_icon varchar(100) null comment '菜单图标',
    menu_view varchar(200) null comment '菜单路径',
    father char(32) null comment '唯一标识',
    weight int not null default 1 comment '权重',
    created_at datetime not null comment '添加时间',
    updated_at datetime null comment '修改时间',
    create_user char(32) not null comment '创建人',
    update_user char(32) null comment '修改人'
)ENGINE=INNODB comment='导航菜单';


-- 角色菜单
create table if not exists role_menu(
    role_menu_id int primary key auto_increment comment '编号',
    role_menu_uuid char(32) not null comment '唯一标识',
    menu_uuid char(32) not null comment '菜单唯一标识',
    role_uuid char(32) not null comment '角色唯一标识',
    auth_time datetime not null comment '授权时间'
)ENGINE=INNODB comment='角色菜单';


-- 系统配置
create table if not exists system_config(
    id int primary key auto_increment comment '编号',
    uuid char(32) unique not null comment '唯一标识',
    model varchar(100) not null default 'Global' comment '所属模块',
    name varchar(50) not null comment '配置名',
    code varchar(50) unique not null comment '配置标识',
    value text not null comment '配置值',
    info varchar(200) null comment '配置说明',
    type tinyint not null comment '配置方式：1:打开模式，2:文本模式，3:选择模式,4:标签模式,5:加密文本,6:对象列表',
    content text null comment '配置选项：Json字符串',
    created_at datetime not null comment '添加时间',
    updated_at datetime null comment '修改时间',
    create_user char(32) not null comment '创建人',
    update_user char(32) null comment '修改人'
) ENGINE=INNODB comment='系统配置';

-- 队列错误日志
create table if not exists `failed_jobs` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
    `queue` text COLLATE utf8mb4_unicode_ci NOT NULL comment '队列',
    `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
    `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL comment '错误',
    `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB comment='队列错误日志' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 用户状态日志
create table if not exists log_user_status(
    id int primary key auto_increment comment '编号',
    status_uuid char(32) not null comment '唯一标识',
    user_uuid char(32) not null  comment '用户编号', -- exists: "users,user_uuid"
    content varchar(200) not null comment '状态内容',
    ip varchar(100) not null comment '状态发生IP',
    remark varchar(200) null comment '备注',
    recover tinyint not null default 0 comment '是否恢复',
    user_status char (1) not null default 1 comment '用户状态，0:失效 2:冻结 3:违规',
    created_at datetime null comment '添加时间',
    status_time bigint not null DEFAULT 0 comment '状态持续时间(毫秒)'
) ENGINE=INNODB charset=utf8mb4 collate=utf8mb4_unicode_ci comment='用户状态日志';


create table if not exists disk(
    id int primary key auto_increment comment '编号',
    uuid char(32) unique not null comment '唯一标识',
    vendor varchar(20) not null default -1 comment '磁盘提供商、-1:当前服务器',
    is_default tinyint not null default 0 comment '是否默认，0-否 1-是',
    name varchar(30) not null comment '磁盘名称',
    access_key_id varchar(255) null comment '访问ID',
    access_key_secret varchar(255) null comment '访问密钥',
    max_size bigint null default 0 comment '最大大小',
    node varchar (100) null comment '磁盘所属节点',
    bucket varchar (100) null comment '存储桶',
    base_path varchar(255) null default '' comment '磁盘根路径',
    access_path varchar(255) not null default -1 comment '访问路径',
    user_uuid char(32) not null comment '所属用户',
    created_at datetime not null comment '添加时间',
    updated_at datetime null comment '修改时间',
    create_user char(32) not null comment '创建人',
    update_user char(32) null comment '修改人'
)ENGINE=INNODB comment='磁盘表';



create table if not exists resources(
    id int primary key auto_increment comment '编号',
    uuid char(32) unique not null comment '唯一标识',
    parent char(32) null default -1 comment '父级',
    parent_all text null  comment '所有父级id',
    disk_uuid char (32) not null comment '所属磁盘',
    name varchar(50) not null comment '资源名',
    type varchar(20) not null default 'directory' comment '资源类型',
    file_type varchar(20) null comment '文件类型',
    file_extension varchar(20) null comment '文件后缀',
    size bigint not null default 0 comment '资源大小',
    cover varchar(200) null comment '封面',
    user_uuid char(32) not null comment '所属用户',
    created_at datetime not null comment '添加时间',
    updated_at datetime null comment '修改时间',
    create_user char(32) not null comment '创建人',
    update_user char(32) null comment '修改人'
)ENGINE=INNODB comment='资源表';


create table if not exists resources_share(
    id int primary key auto_increment comment '编号',
    uuid char(32) unique not null comment '唯一标识',
    resources_uuid char(32) not null comment '资源标识',
    is_public tinyint not null default 0 comment '是否公开',
    secret_key varchar(50) null comment '访问密钥',
    expiration bigint not null default 0 comment '过期时间：0-永久',
    share_time  datetime not null comment '分享时间，用于防止二次分享标识',
    user_uuid char(32) not null comment '所属用户',
    created_at datetime not null comment '添加时间',
    updated_at datetime null comment '修改时间',
    create_user char(32) not null comment '创建人',
    update_user char(32) null comment '修改人'
)ENGINE=INNODB comment='资源分享表';

create table if not exists personal_theme(
    id int primary key auto_increment comment '编号',
    uuid char(32) unique not null comment '唯一标识',
    background_image varchar(200) null comment '背景图片',
    user_uuid char(32) not null comment '所属用户',
    created_at datetime not null comment '添加时间',
    updated_at datetime null comment '修改时间',
    create_user char(32) not null comment '创建人',
    update_user char(32) null comment '修改人'
)ENGINE=INNODB comment='个性化主题表';

-- 省表
create table if not exists map_province(
    province_id int primary key unique comment '省份编号',
    province_name varchar(50) not null comment '省份名称'
);

-- 城市表
create table if not exists map_city(
    city_id int primary key unique comment '城市编号',
    city_name varchar(50) not null comment '城市名称',
    province_id int not null comment '省份编号'
);

-- 区/县表
create table if not exists map_area(
    area_id int primary key unique comment '地区编号',
    area_name varchar(50) not null comment '地区名称',
    city_id int not null comment '城市编号',
    province_id int not null comment '省份编号'
);

-- 街道/镇表
create table if not exists map_street(
    street_id int primary key unique comment '街道编号',
    street_name varchar(50) not null comment '街道名称',
    area_id int not null comment '地区编号',
    city_id int not null comment '城市编号',
    province_id int not null comment '省份编号'
);

-- 四级地址 视图
create view address_level_four
as
select
    map_province.province_id
     ,map_province.province_name
     ,map_city.city_id
     ,map_city.city_name
     ,map_area.area_id
     ,map_area.area_name
     ,map_street.street_id
     ,map_street.street_name
FROM map_province
         JOIN map_city on map_city.province_id = map_province.province_id
         JOIN map_area on map_area.city_id = map_city.city_id
         JOIN map_street on map_street.area_id = map_area.area_id;

-- 三级地址 视图
create view address_level_three
as
select
    map_province.province_id
     ,map_province.province_name
     ,map_city.city_id
     ,map_city.city_name
     ,map_area.area_id
     ,map_area.area_name
FROM map_province
         JOIN map_city on map_city.province_id = map_province.province_id
         JOIN map_area on map_area.city_id = map_city.city_id;
