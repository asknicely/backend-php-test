CREATE TABLE users (
  id INT(11) NOT NULL AUTO_INCREMENT,
  username VARCHAR(255) NOT NULL,
  password VARCHAR(255) NOT NULL,
  PRIMARY KEY (id)
) Engine=InnoDB CHARSET=utf8;

CREATE TABLE todos (
  id INT(11) NOT NULL AUTO_INCREMENT,
  create_date datetime(0) NULL DEFAULT NULL,
  mod_date datetime(0) NULL DEFAULT NULL,
  item_status smallint(1) NULL DEFAULT 0,
  user_id INT(11) NOT NULL,
  description VARCHAR(255),
  PRIMARY KEY (id),
  FOREIGN KEY (user_id) REFERENCES users(id)
) Engine=InnoDB CHARSET=utf8;