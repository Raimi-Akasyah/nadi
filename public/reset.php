<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

post_only();
csrf_verify();
forget_learner();
redirect('index.php?keluar=1');
