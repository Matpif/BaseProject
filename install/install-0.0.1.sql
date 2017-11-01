
CREATE TABLE admin_module
(
  id          INT AUTO_INCREMENT
    PRIMARY KEY,
  module_name VARCHAR(50)            NOT NULL,
  enable      TINYINT(1) DEFAULT '1' NULL,
  CONSTRAINT admin_module_module_name_uindex
  UNIQUE (module_name)
);

INSERT INTO admin_module (module_name, enable) VALUES ('Admin', 1);
INSERT INTO admin_module (module_name, enable) VALUES ('Login', 1);
INSERT INTO admin_module (module_name, enable) VALUES ('Error', 1);
INSERT INTO admin_module (module_name, enable) VALUES ('Install', 0);
INSERT INTO admin_module (module_name, enable) VALUES ('Task', 0);
INSERT INTO admin_module (module_name, enable) VALUES ('Api', 0);
INSERT INTO admin_module (module_name, enable) VALUES ('Cms', 0);
INSERT INTO admin_module (module_name, enable) VALUES ('Ajaxifier', 0);

CREATE TABLE cms_block (
  id                 INT PRIMARY KEY AUTO_INCREMENT,
  name               NVARCHAR(25) NOT NULL,
  language_code      NVARCHAR(5)  NULL,
  title              NVARCHAR(50) NULL,
  content            TEXT         NULL,
  active_page_format BOOLEAN         DEFAULT 0,
  is_enabled         BOOLEAN         DEFAULT 1
);

CREATE UNIQUE INDEX cms_block_name_language_code_uindex
  ON cms_block (name, language_code);

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

CREATE TABLE login_group (
  id    INT PRIMARY KEY AUTO_INCREMENT,
  name  NVARCHAR(50),
  roles TEXT
);

CREATE TABLE login_user
(
  id         INT AUTO_INCREMENT
    PRIMARY KEY,
  username   VARCHAR(50)            NOT NULL,
  password   VARCHAR(50)            NULL,
  group_id   INT                    NOT NULL,
  use_flocon TINYINT(1) DEFAULT '0' NULL,
  totp_key   VARCHAR(255)           NULL,
  CONSTRAINT login_user_username_pk
  UNIQUE (username),
  CONSTRAINT login_user_login_group_id_fk
  FOREIGN KEY (group_id) REFERENCES login_group (id)
);

INSERT INTO login_group (id, name, roles) VALUES (1, 'Admin',
                                                  'Admin_admin,Login_guest,Login_show_users,Login_add_user,Login_delete_user,Login_show_groups,Login_add_group,Login_delete_group');
INSERT INTO login_group (id, name, roles) VALUES (2, 'Guest', 'Login_guest');

/** Mot de passe Admin par défaut: Admin*/
INSERT INTO login_user (username, password, group_id, use_flocon)
VALUES ('Admin', '4e7afebcfbae000b22c7c85e5560f89a2a0280b4', 1, 0);

CREATE TABLE task_task (
  code      NVARCHAR(50) PRIMARY KEY,
  last_exec DATETIME NULL
);