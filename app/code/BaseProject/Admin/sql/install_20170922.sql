CREATE TABLE admin_module
(
  id          INT AUTO_INCREMENT
    PRIMARY KEY,
  module_name VARCHAR(50)            NOT NULL,
  enable      TINYINT(1) DEFAULT '1' NULL,
  CONSTRAINT admin_module_module_name_uindex
  UNIQUE (module_name)
);