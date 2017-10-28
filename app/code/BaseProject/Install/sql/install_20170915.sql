CREATE TABLE install_module
(
  id          INT AUTO_INCREMENT
    PRIMARY KEY,
  module_name VARCHAR(50) NOT NULL,
  CONSTRAINT install_module_module_name_uindex
  UNIQUE (module_name)
);

CREATE TABLE install_file
(
  id        INT AUTO_INCREMENT
    PRIMARY KEY,
  module_id INT         NOT NULL,
  file_name VARCHAR(50) NOT NULL,
  last_exec DATETIME    NULL,
  CONSTRAINT install_file_module_id_file_name_uindex
  UNIQUE (module_id, file_name),
  CONSTRAINT install_file_install_module_id_fk
  FOREIGN KEY (module_id) REFERENCES BaseProject.install_module (id)
);
