CREATE TABLE admin_module (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  project     NVARCHAR(50) DEFAULT 'BaseProject' NOT NULL,
  enable      TINYINT(1) DEFAULT '1'             NULL,
  module_name NVARCHAR(50)                       NOT NULL
);
CREATE TABLE login_user (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  username   VARCHAR(50)            NOT NULL,
  password   VARCHAR(50)            NULL,
  first_name VARCHAR(50)            NULL,
  last_name  VARCHAR(50)            NULL,
  email      VARCHAR(150)           NULL,
  group_id   INT                    NOT NULL,
  use_ldap   TINYINT(1) DEFAULT '0' NULL,
  totp_key   VARCHAR(255)           NULL
);
CREATE TABLE login_group (
  id    INT PRIMARY KEY AUTO_INCREMENT,
  name  NVARCHAR(50),
  roles TEXT
);
CREATE TABLE login_ldap_config (
  id                 INT     AUTO_INCREMENT PRIMARY KEY,
  is_active          TINYINT DEFAULT 0,
  domain_controllers NVARCHAR(250) NULL,
  base_dn            NVARCHAR(250) NULL,
  admin_username     NVARCHAR(250) NULL,
  admin_password     NVARCHAR(250) NULL,
  domain             NVARCHAR(50)  NULL
);
CREATE TABLE install_module (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  module_name VARCHAR(50) NOT NULL
);
CREATE TABLE install_file (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  module_id INT         NOT NULL,
  file_name VARCHAR(50) NOT NULL,
  last_exec DATETIME    NULL
);
CREATE TABLE task_task (
  code      NVARCHAR(50) PRIMARY KEY,
  last_exec DATETIME NULL
);
CREATE TABLE task_scheduler (
  id             INT PRIMARY KEY AUTO_INCREMENT,
  description    NVARCHAR(250)     NULL,
  cron           NVARCHAR(50)      NOT NULL,
  task_code      NVARCHAR(50)      NOT NULL,
  is_enabled     TINYINT DEFAULT 1 NOT NULL,
  last_execution DATETIME          NULL
);
CREATE TABLE task_error (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  scheduler_id INT      NOT NULL,
  code_error   INT      NOT NULL,
  message      TEXT     NULL,
  date         DATETIME NOT NULL
);
CREATE TABLE cms_block (
  id                 INT PRIMARY KEY AUTO_INCREMENT,
  name               NVARCHAR(25) NOT NULL,
  language_code      NVARCHAR(5)  NULL,
  title              NVARCHAR(50) NULL,
  content            TEXT         NULL,
  active_page_format BOOLEAN         DEFAULT 0,
  is_enabled         BOOLEAN         DEFAULT 1
);


INSERT INTO admin_module (module_name, enable) VALUES ('Admin', 1);
INSERT INTO admin_module (module_name, enable) VALUES ('Login', 1);
INSERT INTO admin_module (module_name, enable) VALUES ('Error', 1);
INSERT INTO admin_module (module_name, enable) VALUES ('Install', 0);
INSERT INTO admin_module (module_name, enable) VALUES ('Task', 0);
INSERT INTO admin_module (module_name, enable) VALUES ('Api', 0);
INSERT INTO admin_module (module_name, enable) VALUES ('Cms', 0);
INSERT INTO admin_module (module_name, enable) VALUES ('Ajaxifier', 0);

CREATE UNIQUE INDEX cms_block_name_language_code_uindex
  ON cms_block (name, language_code);

INSERT INTO login_group (id, name, roles) VALUES (1, 'Admin',
                                                  'Admin_admin,Login_guest,Login_show_users,Login_add_user,Login_delete_user,Login_show_groups,Login_add_group,Login_delete_group');
INSERT INTO login_group (id, name, roles) VALUES (2, 'Guest', 'Login_guest');

/** Mot de passe Admin par défaut: Admin*/
INSERT INTO login_user (username, password, group_id, use_ldap)
VALUES ('Admin', '4e7afebcfbae000b22c7c85e5560f89a2a0280b4', 1, 0);

CREATE INDEX task_scheduler_task_task_code_fk
  ON task_scheduler (task_code);
CREATE INDEX task_scheduler_is_enabled_index
  ON task_scheduler (is_enabled);