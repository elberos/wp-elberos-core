-- Adminer 3.6.4 MySQL dump

SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = '+00:00';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `wp_elberos_delivery`;
CREATE TABLE `wp_elberos_delivery` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Notify ID',
  `worker` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Worker for delivery message email, etc ..',
  `plan` varchar(150) CHARACTER SET utf8mb4 NOT NULL DEFAULT 'default' COMMENT 'Delivery plan zakaz, billing, etc ...',
  `status` smallint(6) NOT NULL DEFAULT 0 COMMENT 'Delivery status. 0 - Planned, 1 - Delivered, 2 - Process, -1 - Error',
  `dest` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Destination',
  `uuid` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Delivery uuid',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Title of the message',
  `message` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Message',
  `gmtime_plan` datetime DEFAULT NULL COMMENT 'Time by UTC when delivery task will be started. If null message must be send immediately',
  `gmtime_send` datetime DEFAULT NULL COMMENT 'Time by UTC when message have been sended',
  `attached_files` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Attached files',
  `is_delivered` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Message was delivered',
  `is_read` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'The user has read the message',
  `is_track` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'User went to the link from the letter',
  `error` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Delivery error message',
  `error_code` int(11) NOT NULL DEFAULT 0 COMMENT 'Delivery error code',
  `gmtime_add` datetime NOT NULL COMMENT 'Time of create record by UTC',
  `is_delete` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Delete message after delivery',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`),
  KEY `worker_plan` (`worker`,`plan`),
  KEY `status` (`status`),
  KEY `gmtime_add` (`gmtime_add`),
  KEY `gmtime_send` (`gmtime_send`),
  KEY `plan` (`plan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log of delivered messages';


DROP TABLE IF EXISTS `wp_elberos_forms`;
CREATE TABLE `wp_elberos_forms` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `api_name` varchar(255) NOT NULL,
  `settings` text NOT NULL,
  `email_to` varchar(255) NOT NULL,
  `is_deleted` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `wp_elberos_forms_data`;
CREATE TABLE `wp_elberos_forms_data` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `form_id` bigint(20) DEFAULT NULL,
  `form_title` varchar(255) DEFAULT NULL,
  `data` text NOT NULL,
  `utm` text NOT NULL,
  `send_email_uuid` varchar(255) NOT NULL,
  `send_email_code` tinyint(4) NOT NULL DEFAULT 0,
  `send_email_error` varchar(255) NOT NULL,
  `gmtime_add` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `form_id` (`form_id`),
  CONSTRAINT `wp_elberos_forms_data_ibfk_1` FOREIGN KEY (`form_id`) REFERENCES `wp_elberos_forms` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `wp_elberos_mail_settings`;
CREATE TABLE `wp_elberos_mail_settings` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `enable` tinyint(4) NOT NULL,
  `plan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `host` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `port` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `login` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ssl_enable` tinyint(4) NOT NULL,
  `is_deleted` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plan` (`plan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 2021-01-17 14:35:39