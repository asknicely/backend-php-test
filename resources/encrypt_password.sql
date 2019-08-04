ALTER TABLE users MODIFY password text;

UPDATE users SET password = SHA2(password, 256);
