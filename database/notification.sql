-- Adminer 4.2.5 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `device_token`;
CREATE TABLE `device_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID tự tăng',
  `user_id` int(11) NOT NULL COMMENT 'ID user',
  `platform` enum('ios','android') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Hệ điều hành của thiết bị',
  `imei` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'mã máy',
  `device_token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'token của thiết bị',
  `endpoint_arn` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'endpoint push amazon',
  `is_enabled` tinyint(1) DEFAULT '1' COMMENT 'Có cho phép gửi notification không',
  `created_at` datetime DEFAULT NULL COMMENT 'Thời gian tạo',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Thời gian cập nhật',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `user_id_platform_imei` (`user_id`,`platform`,`imei`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT COMMENT='Thiết bị push notification. Mỗi user hoặc 1 máy là unique';

INSERT INTO `device_token` (`id`, `user_id`, `platform`, `imei`, `device_token`, `endpoint_arn`, `is_enabled`, `created_at`, `updated_at`) VALUES
(1,	1,	'ios',	'2f64a734778b34d0',	'2C83F5EC-6B38-43E5-9C57-D2ED202EA700',	'arn:aws:sns:ap-southeast-1:091772259349:endpoint/APNS/FoxSteps_Prod_APNS/c74e4cfc-f64c-3df6-9d97-89333b83d4ff',	1,	'2020-08-08 01:17:49',	'2020-08-07 18:19:34'),
(2,	1,	'android',	'80275d82ab5e0ce1',	'eZ6SkldqmW8:APA91bGmVu-1wIcDOW3L5ZeZp1Y1m6ROWZj8iOLLVKpGYXG3XQ42yr4V51XQ8doWHvw9ogEZRGFpAf-OrkAe22LPji0QQMWkhXerA0V72B283mOVsOmn71wofalit5DihdJ1kR3f_c7k',	'arn:aws:sns:ap-southeast-1:091772259349:endpoint/GCM/FoxStep_Prod_FCM/e358a6df-0317-38bb-a064-d436ea19c3c9',	1,	'2020-08-08 01:17:49',	'2020-08-07 18:17:49');

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `notification`;
CREATE TABLE `notification` (
  `notification_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID tự tăng',
  `notification_type` enum('default','loyalty','loyalty_level_up','loyalty_adjustment_rank','loyalty_revoke_point') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default' COMMENT 'Loại thông báo. Loyalty sẽ hiển thị popup chi tiết',
  `user_id` int(11) NOT NULL COMMENT 'ID user',
  `notification_avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Avatar của thông báo',
  `notification_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Title',
  `notification_message` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nội dung thông báo',
  `is_read` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Tin nhắn đọc chua',
  `created_at` datetime DEFAULT NULL COMMENT 'Ngày tạo',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Ngày cập nhật',
  PRIMARY KEY (`notification_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT COMMENT='Thông báo';

INSERT INTO `notification` (`notification_id`, `notification_type`, `user_id`, `notification_avatar`, `notification_title`, `notification_message`, `is_read`, `created_at`, `updated_at`) VALUES
(8,	'default',	1,	NULL,	'test',	'mot con vit xoe ra hai cai canh',	0,	'2020-08-08 01:58:01',	'2020-08-07 18:58:01');

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID tự tăng',
  `user_code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mã người dùng',
  `fullname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Họ tên',
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Email',
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Số điện thoại Di động',
  `password` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mật khẩu',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ảnh đại diện',
  `phone_verified` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Đã xác thực số điện thoại',
  `phone_verified_at` datetime DEFAULT NULL COMMENT 'Thời gian xác thực số điện thoại',
  `email_verified` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Đã xác thực email',
  `email_verified_at` datetime DEFAULT NULL COMMENT 'Thời gian xác thực email',
  `is_activated` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Cho phép login',
  `is_deleted` tinyint(1) DEFAULT '0',
  `last_login` datetime DEFAULT NULL COMMENT 'Ngày đăng nhập cuối',
  `last_updated` datetime DEFAULT NULL COMMENT 'Thời gian cập nhật profile',
  `created_at` datetime DEFAULT NULL COMMENT 'Ngày tạo',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Ngày cập nhật',
  PRIMARY KEY (`user_id`) USING BTREE,
  UNIQUE KEY `phone` (`phone`) USING BTREE,
  UNIQUE KEY `user_code` (`user_code`) USING BTREE,
  UNIQUE KEY `email` (`email`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT COMMENT='Người dùng my store';

INSERT INTO `user` (`user_id`, `user_code`, `fullname`, `email`, `phone`, `password`, `avatar`, `phone_verified`, `phone_verified_at`, `email_verified`, `email_verified_at`, `is_activated`, `is_deleted`, `last_login`, `last_updated`, `created_at`, `updated_at`) VALUES
(1,	'abc-234',	'Test user',	'test@gmail.com',	'0901234567',	'7c4a8d09ca3762af61e59520943dc26494f8941b',	NULL,	1,	'2020-08-08 01:16:55',	1,	'2020-08-08 01:16:55',	1,	0,	'2020-08-08 01:16:55',	'2020-08-08 01:16:55',	'2020-08-08 01:16:55',	'2020-08-07 18:16:55');

-- 2020-08-07 19:00:16
