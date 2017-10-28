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