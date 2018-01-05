CREATE TABLE admin_parameter (
  name           NVARCHAR(150) PRIMARY KEY,
  type           ENUM ('string', 'int', 'datetime', 'date', 'text') NOT NULL DEFAULT 'string',
  value_string   NVARCHAR(250)                                      NULL,
  value_int      INT                                                NULL,
  value_datetime DATETIME                                           NULL,
  value_text     TEXT                                               NULL
);
