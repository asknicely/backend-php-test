ALTER TABLE `todos`
	ALTER `description` DROP DEFAULT;
ALTER TABLE `todos`
	CHANGE COLUMN `description` `description` VARCHAR(255) NOT NULL AFTER `user_id`;