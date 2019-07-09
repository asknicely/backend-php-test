ALTER TABLE `todos`
	ADD COLUMN `status` ENUM('pending','complete') NOT NULL DEFAULT 'pending' AFTER `description`;