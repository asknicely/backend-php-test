ALTER TABLE `users` ADD `hash_type` ENUM('plaintext','bcrypt') NOT NULL DEFAULT 'plaintext' AFTER `password`;
ALTER TABLE `users` ADD `salt` VARCHAR(16) NOT NULL DEFAULT '' AFTER `hash_type`;