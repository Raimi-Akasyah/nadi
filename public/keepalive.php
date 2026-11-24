<?php
declare(strict_types=1);

// Keep-alive from the course page (assets/js/course.js): slide changes and other activity happen in the
// browser without a page request, so this refreshes the idle timeout while the learner is reading.
require __DIR__ . '/../src/bootstrap.php';
post_only();
csrf_verify();

header('Cache-Control: no-store');
http_response_code(current_learner() !== null ? 204 : 401);
