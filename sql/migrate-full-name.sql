-- NADI LMS migration: one "Nama Penuh" (full name) field instead of first name + last name.
-- Import into the existing database AFTER sql/schema.sql and sql/migrate-module-quizzes.sql
-- (e.g. via phpMyAdmin -> Import). Compatible with MySQL 5.7+, MySQL 8 and MariaDB 10.3+.
--
-- What it does:
-- - Adds learners.full_name VARCHAR(200) NOT NULL DEFAULT '' (before first_name) and fills it for every
--   existing learner with first_name + ' ' + last_name, so their certificates show the same name as before.
-- - Gives first_name and last_name the default '' (new learners only fill full_name).
-- - Adds the indexes idx_learners_email and idx_learners_full_name, used by "Sambung Semula".
-- Nothing is deleted: first_name and last_name keep their values.
--
-- Safe to import twice: each change runs only when it is missing (checked in information_schema), and only
-- empty full_name values are filled.

SET @has_col := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'learners' AND COLUMN_NAME = 'full_name'
);
SET @sql := IF(@has_col = 0,
    'ALTER TABLE learners ADD COLUMN full_name VARCHAR(200) NOT NULL DEFAULT '''' AFTER id',
    'SELECT ''full_name already exists'' AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

ALTER TABLE learners
    MODIFY first_name VARCHAR(100) NOT NULL DEFAULT '',
    MODIFY last_name VARCHAR(100) NOT NULL DEFAULT '';

UPDATE learners
SET full_name = TRIM(CONCAT(TRIM(first_name), ' ', TRIM(last_name)))
WHERE full_name = '';

SET @has_idx := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'learners' AND INDEX_NAME = 'idx_learners_email'
);
SET @sql := IF(@has_idx = 0,
    'ALTER TABLE learners ADD KEY idx_learners_email (email)',
    'SELECT ''idx_learners_email already exists'' AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_idx := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'learners' AND INDEX_NAME = 'idx_learners_full_name'
);
SET @sql := IF(@has_idx = 0,
    'ALTER TABLE learners ADD KEY idx_learners_full_name (full_name(100))',
    'SELECT ''idx_learners_full_name already exists'' AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
