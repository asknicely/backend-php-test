ALTER TABLE todos CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE todos MODIFY description varchar(255) CHARSET utf8mb4;

ALTER TABLE todos ADD COLUMN is_completed TINYINT(1) DEFAULT 0 AFTER description;
