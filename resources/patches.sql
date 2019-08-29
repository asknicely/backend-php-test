ALTER TABLE todos CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE todos modify description varchar(255) charset utf8mb4;
