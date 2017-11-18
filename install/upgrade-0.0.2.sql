ALTER TABLE admin_module ADD project NVARCHAR(50) DEFAULT 'BaseProject' NOT NULL;
ALTER TABLE admin_module
  MODIFY COLUMN module_name VARCHAR(50) NOT NULL AFTER project,
  MODIFY COLUMN enable TINYINT(1) DEFAULT '1' AFTER project;