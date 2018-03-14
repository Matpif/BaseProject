ALTER TABLE admin_parameter MODIFY COLUMN type enum('string', 'int', 'datetime', 'date', 'text', 'select') NOT NULL default 'string';
