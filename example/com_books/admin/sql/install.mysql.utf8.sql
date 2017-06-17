CREATE TABLE IF NOT EXISTS `#__book` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`catid` int(11) unsigned NOT NULL DEFAULT '0',
	`title` VARCHAR(512) NOT NULL,
	`authors` VARCHAR(512) NOT NULL,
	`description` TEXT NOT NULL COMMENT 'Details',
	`ordering` int(11) NOT NULL DEFAULT '0',
	`published` tinyint(3) NOT NULL DEFAULT '0',
	`checked_out` int(11) unsigned NOT NULL DEFAULT '0',
	`checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` int(11) unsigned NOT NULL DEFAULT '0',
	`created_by_alias` varchar(255) NOT NULL DEFAULT '',
	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`images` text NOT NULL,
	`hits` int(11) NOT NULL DEFAULT '0',
	`access` int(11) unsigned NOT NULL DEFAULT '0',
	`language` char(7) NOT NULL COMMENT 'The language code for the article.',
	PRIMARY KEY (id)
)
CHARACTER SET utf8
COLLATE utf8_general_ci;
