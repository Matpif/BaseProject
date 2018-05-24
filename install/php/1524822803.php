<?php $export = array (
  'BaseProject\\Admin\\Model\\Module' => 
  array (
    'table_name' => 'admin_module',
    'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
    'project' => 'nvarchar(50) DEFAULT \'BaseProject\' NOT NULL',
    'enable' => 'tinyint(1) DEFAULT \'1\' NULL',
    'module_name' => 'nvarchar(50) NOT NULL',
  ),
  'BaseProject\\Admin\\Model\\Parameter' => 
  array (
    'table_name' => 'admin_parameter',
    'name' => 'nvarchar(150) PRIMARY KEY',
    'type' => 'enum(\'string\', \'int\', \'datetime\', \'date\', \'text\', \'select\') NOT NULL default \'string\'',
    'value_string' => 'nvarchar(250) NULL',
    'value_int' => 'int NULL',
    'value_datetime' => 'datetime NULL',
    'value_text' => 'text NULL',
  ),
  'BaseProject\\Login\\Model\\User' => 
  array (
    'table_name' => 'login_user',
    'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
    'username' => 'VARCHAR(50) NOT NULL',
    'password' => 'VARCHAR(50) NULL',
    'first_name' => 'VARCHAR(50) NULL',
    'last_name' => 'VARCHAR(50) NULL',
    'email' => 'VARCHAR(150) NULL',
    'group_id' => 'INT NOT NULL',
    'use_ldap' => 'TINYINT(1) DEFAULT \'0\' NULL',
    'totp_key' => 'VARCHAR(255) NULL',
  ),
  'BaseProject\\Login\\Model\\Group' => 
  array (
    'table_name' => 'login_group',
    'id' => 'INT PRIMARY KEY AUTO_INCREMENT',
    'name' => 'NVARCHAR(50)',
    'roles' => 'TEXT',
  ),
  'BaseProject\\Login\\Model\\LdapConfig' => 
  array (
    'table_name' => 'login_ldap_config',
    'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
    'is_active' => 'TINYINT DEFAULT 0',
    'domain_controllers' => 'NVARCHAR(250) NULL',
    'base_dn' => 'NVARCHAR(250) NULL',
    'admin_username' => 'NVARCHAR(250) NULL',
    'admin_password' => 'NVARCHAR(250) NULL',
    'domain' => 'NVARCHAR(50) NULL',
  ),
  'BaseProject\\Install\\Model\\Module' => 
  array (
    'table_name' => 'install_module',
    'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
    'module_name' => 'VARCHAR(50) NOT NULL',
  ),
  'BaseProject\\Install\\Model\\File' => 
  array (
    'table_name' => 'install_file',
    'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
    'module_id' => 'INT NOT NULL',
    'file_name' => 'VARCHAR(50) NOT NULL',
    'last_exec' => 'DATETIME NULL',
  ),
  'BaseProject\\Task\\Model\\Task' => 
  array (
    'table_name' => 'task_task',
    'code' => 'NVARCHAR(50) PRIMARY KEY',
    'last_exec' => 'DATETIME NULL',
  ),
  'BaseProject\\Task\\Model\\Scheduler' => 
  array (
    'table_name' => 'task_scheduler',
    'id' => 'INT PRIMARY KEY AUTO_INCREMENT',
    'description' => 'NVARCHAR(250) NULL',
    'cron' => 'NVARCHAR(50) NOT NULL',
    'task_code' => 'NVARCHAR(50) NOT NULL',
    'is_enabled' => 'TINYINT DEFAULT 1 NOT NULL',
    'last_execution' => 'DATETIME NULL',
  ),
  'BaseProject\\Task\\Model\\Error' => 
  array (
    'table_name' => 'task_error',
    'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
    'scheduler_id' => 'INT NOT NULL',
    'code_error' => 'INT NOT NULL',
    'message' => 'TEXT NULL',
    'date' => 'DATETIME NOT NULL',
  ),
  'BaseProject\\Cms\\Model\\Block' => 
  array (
    'table_name' => 'cms_block',
    'id' => 'INT PRIMARY KEY AUTO_INCREMENT',
    'name' => 'NVARCHAR(25) NOT NULL',
    'language_code' => 'NVARCHAR(5) NULL',
    'title' => 'NVARCHAR(50) NULL',
    'content' => 'TEXT NULL',
    'active_page_format' => 'BOOLEAN DEFAULT 0',
    'is_enabled' => 'BOOLEAN DEFAULT 1',
  ),
  'BaseProject\\Rewrite\\Model\\Rewrite' => 
  array (
    'table_name' => 'rewrite_rewrite',
    'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
    'name' => 'varchar(50) not null',
    'basic_url' => 'varchar(500) not null',
    'rewrite_url' => 'varchar(500) not null',
  ),
);