-- NADI LMS schema. Import into an existing database (e.g. via phpMyAdmin -> Import).
-- Compatible with MySQL 5.7+, MySQL 8 and MariaDB 10.3+.

CREATE TABLE IF NOT EXISTS learners (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    age TINYINT UNSIGNED NOT NULL,
    email VARCHAR(190) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address VARCHAR(255) NOT NULL,
    occupation VARCHAR(100) NOT NULL,
    token_hash CHAR(64) NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_learners_token_hash (token_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Extra "Ingat saya" tokens, one per device where a learner resumed by e-mail with the box ticked.
-- (The token from registration stays in learners.token_hash.)
CREATE TABLE IF NOT EXISTS learner_tokens (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    learner_id INT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_learner_tokens_hash (token_hash),
    KEY idx_learner_tokens_learner (learner_id),
    CONSTRAINT fk_learner_tokens_learner FOREIGN KEY (learner_id)
        REFERENCES learners (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- "Sambung Semula" (resume by e-mail) attempts, for rate limiting. IP and e-mail are stored as SHA-256 hashes.
CREATE TABLE IF NOT EXISTS resume_attempts (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    ip_hash CHAR(64) NOT NULL,
    email_hash CHAR(64) NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_resume_ip (ip_hash, created_at),
    KEY idx_resume_email (email_hash, created_at),
    KEY idx_resume_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS lesson_progress (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    learner_id INT UNSIGNED NOT NULL,
    lesson_key VARCHAR(100) NOT NULL,
    completed_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_progress_learner_lesson (learner_id, lesson_key),
    CONSTRAINT fk_progress_learner FOREIGN KEY (learner_id)
        REFERENCES learners (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- quiz_key = the quiz lesson key (kuiz-modul-1 ... kuiz-modul-5). 'kuiz-akhir' marks attempts of the old
-- single final quiz (all 10 questions); a passed one counts as all module quizzes passed.
-- Existing databases: import sql/migrate-module-quizzes.sql to add this column.
CREATE TABLE IF NOT EXISTS quiz_attempts (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    learner_id INT UNSIGNED NOT NULL,
    course_key VARCHAR(64) NOT NULL,
    quiz_key VARCHAR(64) NOT NULL DEFAULT 'kuiz-akhir',
    correct_count TINYINT UNSIGNED NOT NULL,
    total_questions TINYINT UNSIGNED NOT NULL,
    score_percent DECIMAL(5,2) NOT NULL,
    passed TINYINT(1) NOT NULL DEFAULT 0,
    answers_json TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_attempts_learner_course (learner_id, course_key),
    KEY idx_attempts_learner_quiz (learner_id, quiz_key),
    CONSTRAINT fk_attempts_learner FOREIGN KEY (learner_id)
        REFERENCES learners (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS completions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    learner_id INT UNSIGNED NOT NULL,
    course_key VARCHAR(64) NOT NULL,
    quiz_attempt_id INT UNSIGNED NOT NULL,
    certificate_code VARCHAR(32) NOT NULL,
    completed_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_completions_code (certificate_code),
    UNIQUE KEY uq_completions_learner_course (learner_id, course_key),
    KEY idx_completions_attempt (quiz_attempt_id),
    CONSTRAINT fk_completions_learner FOREIGN KEY (learner_id)
        REFERENCES learners (id) ON DELETE CASCADE,
    CONSTRAINT fk_completions_attempt FOREIGN KEY (quiz_attempt_id)
        REFERENCES quiz_attempts (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
