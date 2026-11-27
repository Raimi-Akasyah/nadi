<?php
/** @var array $dash admin_dashboard() */
/** @var array $rows admin_learner_summaries() */
/** @var int $total */
/** @var int $page */
/** @var int $pages */
/** @var string $search */
/** @var string $status */
require_once __DIR__ . '/icons.php';

$pct = static function (int $part, int $whole): string {
    return $whole > 0 ? (string)round($part * 100 / $whole) . '%' : '-';
};
$query = static function (array $extra) use ($search, $status): string {
    return 'admin.php?' . http_build_query(array_filter(['q' => $search, 'status' => $status] + $extra, static function ($v): bool {
        return $v !== '' && $v !== null;
    }));
};
/** @var Course[] $tracks */
$tracks = $dash['tracks'];
?>
<main class="admin-shell">
    <div class="admin-head">
        <div>
            <p class="eyebrow">Penganjur</p>
            <h1>Panel Admin</h1>
        </div>
        <div class="admin-actions">
            <a class="btn btn-primary" href="<?= e($query(['export' => 'csv'])) ?>"><?= icon('download') ?>Eksport CSV</a>
            <form method="post" action="admin.php" class="inline-form">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="logout">
                <button type="submit" class="btn btn-outline">Log Keluar</button>
            </form>
        </div>
    </div>

    <ul class="admin-stats">
        <li class="admin-stat">
            <span class="admin-stat-label">Peserta berdaftar</span>
            <span class="admin-stat-num"><?= e($dash['learners']) ?></span>
            <span class="admin-stat-sub">+<?= e($dash['learners_new']) ?> dalam 7 hari lepas</span>
        </li>
        <li class="admin-stat is-success">
            <span class="admin-stat-label">Sijil dikeluarkan</span>
            <span class="admin-stat-num"><?= e($dash['certificates']) ?></span>
            <span class="admin-stat-sub"><?= e($pct($dash['certificates'], $dash['learners'])) ?> daripada peserta</span>
        </li>
        <?php foreach ($tracks as $t): ?>
            <li class="admin-stat">
                <span class="admin-stat-label">Mula <?= e($t->trackTitle()) ?></span>
                <span class="admin-stat-num"><?= e($dash['started'][$t->track()] ?? 0) ?></span>
                <span class="admin-stat-sub"><?= e($dash['cert_tracks'][$t->track()] ?? 0) ?> sijil melalui <?= e($t->trackTitle()) ?></span>
            </li>
        <?php endforeach; ?>
    </ul>
    <p class="muted small admin-note"><?= icon('info') ?>"Mula" bermaksud peserta telah menyelesaikan sekurang-kurangnya satu pelajaran dalam cara belajar itu.</p>

    <div class="admin-grid">
        <section class="card admin-card" aria-labelledby="kuiz-heading">
            <h2 id="kuiz-heading">Prestasi Kuiz</h2>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr><th scope="col">Kuiz</th><th scope="col">Mencuba</th><th scope="col">Lulus</th><th scope="col">Percubaan</th><th scope="col">Kadar lulus</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dash['quizzes'] as $q): ?>
                            <tr>
                                <th scope="row"><?= e($q['title']) ?> <span class="admin-tag"><?= e($q['track']) ?></span></th>
                                <td><?= e($q['learners']) ?></td>
                                <td><?= e($q['passed_learners']) ?></td>
                                <td><?= e($q['attempts']) ?></td>
                                <td><?= e($pct($q['passes'], $q['attempts'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="muted small">Kadar lulus = percubaan yang lulus daripada semua percubaan.</p>
        </section>

        <section class="card admin-card" aria-labelledby="soalan-heading">
            <h2 id="soalan-heading">Soalan Paling Kerap Salah</h2>
            <?php if ($dash['questions'] === []): ?>
                <p class="muted">Belum ada jawapan salah untuk dipaparkan.</p>
            <?php else: ?>
                <ol class="admin-questions">
                    <?php foreach ($dash['questions'] as $s): ?>
                        <li>
                            <p class="admin-question-text"><?= e($s['text']) ?></p>
                            <div class="admin-bar" aria-hidden="true"><span style="width: <?= e($s['percent']) ?>%"></span></div>
                            <p class="muted small"><?= e($s['percent']) ?>% salah (<?= e($s['wrong']) ?> daripada <?= e($s['asked']) ?> jawapan) &middot; <?= e($s['quizzes']) ?></p>
                        </li>
                    <?php endforeach; ?>
                </ol>
                <p class="muted small">Daripada 5000 percubaan terkini. Soalan yang kerap salah mungkin perlu ditulis semula atau diterangkan dengan lebih jelas dalam modul.</p>
            <?php endif; ?>
        </section>
    </div>

    <section class="card admin-card" aria-labelledby="peserta-heading">
        <div class="admin-card-head">
            <h2 id="peserta-heading">Peserta <span class="admin-count">(<?= e($total) ?>)</span></h2>
            <form method="get" action="admin.php" class="admin-filters" role="search">
                <label class="sr-only" for="ad-q">Cari peserta</label>
                <input id="ad-q" type="search" name="q" value="<?= e($search) ?>" maxlength="100" placeholder="Cari nama, e-mel atau pusat NADI">
                <label class="sr-only" for="ad-status">Status</label>
                <select id="ad-status" name="status">
                    <option value=""<?= $status === '' ? ' selected' : '' ?>>Semua peserta</option>
                    <option value="sijil"<?= $status === 'sijil' ? ' selected' : '' ?>>Ada sijil</option>
                    <option value="belum"<?= $status === 'belum' ? ' selected' : '' ?>>Belum ada sijil</option>
                </select>
                <button type="submit" class="btn btn-outline btn-sm"><?= icon('search') ?>Cari</button>
            </form>
        </div>
        <?php if ($rows === []): ?>
            <p class="muted">Tiada peserta dijumpai.</p>
        <?php else: ?>
            <div class="admin-table-wrap">
                <table class="admin-table admin-learners">
                    <thead>
                        <tr>
                            <th scope="col">Nama</th>
                            <th scope="col">E-mel</th>
                            <th scope="col">Pusat NADI</th>
                            <th scope="col">Pekerjaan</th>
                            <th scope="col">Daftar</th>
                            <?php foreach ($tracks as $t): ?><th scope="col"><?= e($t->trackTitle()) ?></th><?php endforeach; ?>
                            <th scope="col">Kuiz lulus</th>
                            <th scope="col">Sijil</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r):
                            $l = $r['learner'];
                            $c = $r['certificate'];
                        ?>
                            <tr>
                                <th scope="row"><?= e($r['name']) ?><span class="admin-sub"><?= e(implode(' · ', array_filter([(string)($l['gender'] ?? ''), 'Umur ' . $l['age']], 'strlen'))) ?></span></th>
                                <td><?= e($l['email']) ?></td>
                                <td><?= e(learner_nadi_label(['nadi_name' => $l['nadi_name'] ?? '']) ?: '-') ?>
                                    <span class="admin-sub"><?= e(implode(', ', array_filter([(string)($l['district'] ?? ''), (string)($l['state'] ?? '')], 'strlen'))) ?><?= ($l['nadi_name'] ?? '') !== '' && ($l['nadi_code'] ?? '') === '' ? ' · Lain-lain' : '' ?></span></td>
                                <td><?= e($l['occupation']) ?></td>
                                <td class="admin-nowrap"><?= e(format_date_ms((string)$l['created_at'])) ?></td>
                                <?php foreach ($tracks as $t):
                                    $p = $r['percent'][$t->track()];
                                ?>
                                    <td><div class="admin-bar<?= $p >= 100 ? ' is-complete' : '' ?>" aria-hidden="true"><span style="width: <?= e($p) ?>%"></span></div><?= e($p) ?>%</td>
                                <?php endforeach; ?>
                                <td><?= e($r['quizzes_passed']) ?><span class="admin-sub"><?= e($r['attempts']) ?> percubaan</span></td>
                                <td>
                                    <?php if ($c !== null): ?>
                                        <a class="cert-code" href="verify.php?<?= e(http_build_query(['code' => $c['code']])) ?>"><?= e($c['code']) ?></a>
                                        <span class="admin-sub"><?= e(format_date_ms($c['date'])) ?> &middot; <?= e($c['track']) ?></span>
                                    <?php else: ?>
                                        <span class="muted">Belum</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($pages > 1): ?>
                <nav class="admin-pager" aria-label="Halaman senarai peserta">
                    <?php if ($page > 1): ?><a class="btn btn-nav btn-sm" href="<?= e($query(['page' => $page - 1])) ?>"><?= icon('prev') ?>Sebelum</a><?php endif; ?>
                    <span>Halaman <?= e($page) ?> daripada <?= e($pages) ?></span>
                    <?php if ($page < $pages): ?><a class="btn btn-nav btn-sm" href="<?= e($query(['page' => $page + 1])) ?>">Seterusnya<?= icon('next') ?></a><?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</main>
