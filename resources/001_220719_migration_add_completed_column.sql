ALTER TABLE `todos` ADD `completed` TINYINT(1) NOT NULL DEFAULT '0' AFTER `description`;
ALTER TABLE `todos` ADD INDEX(`completed`);