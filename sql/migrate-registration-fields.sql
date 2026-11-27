-- NADI LMS migration: registration asks for Jantina, Pekerjaan (a list), Negeri, Daerah and Pusat NADI, and no
-- longer for the phone number and address.
-- Import into the existing database AFTER sql/migrate-full-name.sql (e.g. via phpMyAdmin -> Import), BEFORE
-- uploading the new files. Compatible with MySQL 5.7+, MySQL 8 and MariaDB 10.3+.
--
-- What it does:
-- - Adds learners.gender, state, district, nadi_code and nadi_name (all VARCHAR, default '') and the index
--   idx_learners_nadi. Existing learners get empty values (they registered before these fields).
-- - Gives phone and address the default '' (new learners leave them empty). Nothing is deleted: existing phone
--   numbers and addresses stay in the table.
--
-- Safe to import twice: each column and the index are added only when missing (checked in information_schema).

SET @has_col := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'learners' AND COLUMN_NAME = 'gender'
);
SET @sql := IF(@has_col = 0,
    'ALTER TABLE learners ADD COLUMN gender VARCHAR(20) NOT NULL DEFAULT '''' AFTER age',
    'SELECT ''gender already exists'' AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_col := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'learners' AND COLUMN_NAME = 'state'
);
SET @sql := IF(@has_col = 0,
    'ALTER TABLE learners ADD COLUMN state VARCHAR(60) NOT NULL DEFAULT '''' AFTER occupation',
    'SELECT ''state already exists'' AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_col := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'learners' AND COLUMN_NAME = 'district'
);
SET @sql := IF(@has_col = 0,
    'ALTER TABLE learners ADD COLUMN district VARCHAR(100) NOT NULL DEFAULT '''' AFTER state',
    'SELECT ''district already exists'' AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_col := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'learners' AND COLUMN_NAME = 'nadi_code'
);
SET @sql := IF(@has_col = 0,
    'ALTER TABLE learners ADD COLUMN nadi_code VARCHAR(20) NOT NULL DEFAULT '''' AFTER district',
    'SELECT ''nadi_code already exists'' AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_col := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'learners' AND COLUMN_NAME = 'nadi_name'
);
SET @sql := IF(@has_col = 0,
    'ALTER TABLE learners ADD COLUMN nadi_name VARCHAR(150) NOT NULL DEFAULT '''' AFTER nadi_code',
    'SELECT ''nadi_name already exists'' AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


ALTER TABLE learners
    MODIFY phone VARCHAR(20) NOT NULL DEFAULT '',
    MODIFY address VARCHAR(255) NOT NULL DEFAULT '';

SET @has_idx := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'learners' AND INDEX_NAME = 'idx_learners_nadi'
);
SET @sql := IF(@has_idx = 0,
    'ALTER TABLE learners ADD KEY idx_learners_nadi (state, district, nadi_code)',
    'SELECT ''idx_learners_nadi already exists'' AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
