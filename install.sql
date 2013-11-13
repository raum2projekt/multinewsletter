CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%375_archive` (
  `id` smallint(4) unsigned NOT NULL auto_increment,
  `clang` tinyint(4) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `htmlbody` text NOT NULL,
  `textbody` text NOT NULL,
  `format` varchar(10) NOT NULL,
  `recipients` longtext NOT NULL,
  `groupname` varchar(255) NOT NULL,
  `gid` smallint(4) unsigned NOT NULL,
  `setupdate` int(11) NOT NULL,
  `sentdate` int(11) NOT NULL,
  `sentby` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `setupdate` (`setupdate`,`clang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%375_group` (
  `id` smallint(4) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `createdate` int(11) NOT NULL,
  `updatedate` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%375_user` (
  `id` smallint(4) unsigned NOT NULL auto_increment,
  `email` varchar(255) NOT NULL,
  `grad` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `title` tinyint(4) NOT NULL,
  `clang` tinyint(4) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `article_id` smallint(4) unsigned NOT NULL,
  `send_group` smallint(4) unsigned NOT NULL,
  `createdate` int(11) NOT NULL,
  `createip` varchar(16) NOT NULL,
  `activationdate` int(11) NOT NULL,
  `activationip` varchar(16) NOT NULL,
  `updatedate` int(11) NOT NULL,
  `updateip` varchar(16) NOT NULL,
  `subscriptiontype` varchar(16) NOT NULL,
  `key` varchar(6) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%375_user2group` (
  `uid` smallint(4) unsigned NOT NULL,
  `gid` smallint(4) unsigned NOT NULL,
  UNIQUE KEY `uid` (`uid`,`gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;