CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%375_archive` (
	`archive_id` int(11) unsigned NOT NULL auto_increment,
	`clang_id` int(11) NOT NULL,
	`subject` varchar(255) NOT NULL,
	`htmlbody` longtext NOT NULL,
	`recipients` longtext NOT NULL,
	`group_ids` text NOT NULL,
	`sender_email` varchar(255) NOT NULL,
	`sender_name` varchar(255) NOT NULL,
	`setupdate` int(11) NOT NULL,
	`sentdate` int(11) NOT NULL,
	`sentby` varchar(255) NOT NULL,
PRIMARY KEY(`archive_id`),
UNIQUE KEY `setupdate` (`setupdate`,`clang_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%375_group` (
	`group_id` int(11) unsigned NOT NULL auto_increment,
	`name` varchar(255) NOT NULL,
	`default_sender_email` varchar(255) NOT NULL,
	`default_sender_name` varchar(255) NOT NULL,
	`default_article_id` int(11) unsigned NOT NULL,
	`createdate` int(11) NOT NULL,
	`updatedate` int(11) NOT NULL,
PRIMARY KEY(`group_id`),
UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%375_user` (
	`user_id` int(11) unsigned NOT NULL auto_increment,
	`email` varchar(255) NOT NULL,
	`grad` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
	`firstname` varchar(255) NOT NULL,
	`lastname` varchar(255) NOT NULL,
	`title` tinyint(4) NOT NULL,
	`clang_id` int(11) NOT NULL,
	`status` tinyint(1) NOT NULL,
	`group_ids` text NOT NULL,
	`send_archive_id` tinyint(1) unsigned NOT NULL,
	`createdate` int(11) NOT NULL,
	`createip` varchar(45) NOT NULL,
	`activationdate` int(11) NOT NULL,
	`activationip` varchar(45) NOT NULL,
	`updatedate` int(11) NOT NULL,
	`updateip` varchar(45) NOT NULL,
	`subscriptiontype` varchar(16) NOT NULL,
	`activationkey` int(6) NOT NULL,
PRIMARY KEY(`user_id`),
UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `%TABLE_PREFIX%template` CHANGE `content` `content` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;