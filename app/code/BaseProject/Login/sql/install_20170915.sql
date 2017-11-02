CREATE TABLE login_group (
  id    INT PRIMARY KEY AUTO_INCREMENT,
  name  NVARCHAR(50),
  roles TEXT
);
CREATE TABLE login_user
(
  id         INT     AUTO_INCREMENT
    PRIMARY KEY,
  username   VARCHAR(50) NOT NULL,
  password   VARCHAR(50) NULL,
  group_id   INT         NOT NULL,
  use_ldap BOOLEAN DEFAULT 0,
  CONSTRAINT login_user_login_group_id_fk
  FOREIGN KEY (group_id) REFERENCES BaseProject.login_group (id)
);
CREATE INDEX login_user_login_group_id_fk
  ON login_user (group_id);


INSERT INTO login_group (id, name, roles) VALUES (1, 'Admin',
                                                  'Admin_admin,Login_guest,Login_show_users,Login_add_user,Login_delete_user,Login_show_groups,Login_add_group,Login_delete_group');
/** Mot de passe Admin par défaut: Admin*/
INSERT INTO login_user (username, password, group_id, use_ldap)
VALUES ('Admin', '4e7afebcfbae000b22c7c85e5560f89a2a0280b4', 1, 0);

ALTER TABLE login_user
  ADD COLUMN totp_key VARCHAR(255) NULL;