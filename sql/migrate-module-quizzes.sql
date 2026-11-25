-- NADI LMS migration: one quiz per module (Kuiz Modul 1 ... Kuiz Modul 5).
-- Import into the existing database AFTER sql/schema.sql (e.g. via phpMyAdmin -> Import).
-- Compatible with MySQL 5.7+, MySQL 8 and MariaDB 10.3+.
--
-- What it does:
-- - Adds quiz_attempts.quiz_key VARCHAR(64) NOT NULL DEFAULT 'kuiz-akhir' (after course_key).
--   Every existing attempt gets 'kuiz-akhir', the old single final quiz. A learner with a passed
--   'kuiz-akhir' attempt or a completion counts as having passed all module quizzes.
-- - Adds the index idx_attempts_learner_quiz (learner_id, quiz_key).
-- Nothing is deleted or rewritten: learners, lesson progress, attempts, completions and
-- certificate codes stay as they are.
--
-- Safe to import twice: each change runs only when it is missing (checked in information_schema).

SET @has_col := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'quiz_attempts' AND COLUMN_NAME = 'quiz_key'
);
SET @sql := IF(@has_col = 0,
    'ALTER TABLE quiz_attempts ADD COLUMN quiz_key VARCHAR(64) NOT NULL DEFAULT ''kuiz-akhir'' AFTER course_key',
    'SELECT ''quiz_key already exists'' AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_idx := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'quiz_attempts' AND INDEX_NAME = 'idx_attempts_learner_quiz'
);
SET @sql := IF(@has_idx = 0,
    'ALTER TABLE quiz_attempts ADD KEY idx_attempts_learner_quiz (learner_id, quiz_key)',
    'SELECT ''idx_attempts_learner_quiz already exists'' AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
