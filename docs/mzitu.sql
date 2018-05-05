USE `mzitu`;

-- 分类表
DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `category` varchar(255) NOT NULL COMMENT '分类名称',
    `short_name` varchar(32) NOT NULL COMMENT '分类缩写',

    `create_time` int NOT NULL COMMENT '记录创建时间',
    `update_time` int NOT NULL COMMENT '记录更新时间',
    PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='分类表';

-- album
DROP TABLE IF EXISTS `album`;
CREATE TABLE `album` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `album_id` bigint(20) NOT NULL COMMENT '专辑id',
    `total_pic` int(10) NOT NULL DEFAULT 0 COMMENT '图片数量',
    `title` varchar(1024) NOT NULL COMMENT '作者名',
    `category_id` int NOT NULL COMMENT '分类id',
    `create_at` int NOT NULL COMMENT '专辑创建时间',
    `process_status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '处理状态 0 未处理 1 处理完成',
    `info_status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '信息补全状态 0 未处理 1 处理完成',

    `create_time` int NOT NULL COMMENT '记录创建时间',
    `update_time` int NOT NULL COMMENT '记录更新时间',

    PRIMARY KEY (`id`),
    KEY `album_id` (`album_id`),
    UNIQUE KEY `category_album` (`category_id`, `album_id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='专辑表';

-- 图片表
DROP TABLE IF EXISTS `pic`;
CREATE TABLE `pic` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `album_id` bigint(20) NOT NULL COMMENT '专辑id',
    `rank` int(10) NOT NULL COMMENT '排序',
    `url` varchar(1024) NOT NULL COMMENT 'url',
    `dl_status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否下载',
    `file_name` varchar(1024) NOT NULL DEFAULT '' COMMENT '下载文件名称',
    `file_size` int NOT NULL DEFAULT 0 COMMENT '文件大小',

    `create_time` int NOT NULL COMMENT '记录创建时间',
    `update_time` int NOT NULL COMMENT '记录更新时间',

    PRIMARY KEY (`id`),
    UNIQUE KEY `album_rank` (`album_id`, `rank`),
    KEY `album` (`album_id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='图片表';
