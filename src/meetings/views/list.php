<?php

use ChurchCRM\dto\SystemURLs;
use ChurchCRM\Utils\InputUtils;

require SystemURLs::getDocumentRoot() . '/Include/Header.php';
?>

<style nonce="<?= SystemURLs::getCSPNonce() ?>">
    .meetings-list-premium { --ml-primary:#166c5d; --ml-primary-dark:#0c4d42; --ml-soft:#e8f4f0; --ml-ink:#17251f; --ml-muted:#66756e; --ml-line:#e5ece8; --ml-canvas:#f5f8f6; --ml-radius:16px; animation:ml-enter .35s ease-out both; background:var(--ml-canvas); border-radius:var(--ml-radius); color:var(--ml-ink); padding:1.25rem; }
    @keyframes ml-enter { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }
    .meetings-list-hero { background:linear-gradient(118deg,var(--ml-primary-dark),var(--ml-primary) 64%,#258976); border-radius:20px; box-shadow:0 12px 30px rgba(11,77,66,.18); color:#fff; margin-bottom:1rem; overflow:hidden; padding:1.45rem 1.5rem; position:relative; }
    .meetings-list-hero::after { border:1px solid rgba(255,255,255,.15); border-radius:50%; content:''; height:280px; pointer-events:none; position:absolute; right:-75px; top:-145px; width:280px; }
    .meetings-list-hero > * { position:relative; z-index:1; }
    .meetings-list-kicker { color:rgba(255,255,255,.76); font-size:.72rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; }
    .meetings-list-title { font-size:clamp(1.45rem,2.7vw,2.05rem); font-weight:700; letter-spacing:-.04em; line-height:1.12; margin:.35rem 0 .65rem; }
    .meetings-list-copy { color:rgba(255,255,255,.86); font-size:.92rem; margin:0; }
    .meetings-list-count { backdrop-filter:blur(8px); background:rgba(255,255,255,.13); border:1px solid rgba(255,255,255,.18); border-radius:13px; display:inline-block; padding:.7rem .85rem; }
    .meetings-list-count strong { display:block; font-size:1.35rem; line-height:1; }
    .meetings-list-count span { color:rgba(255,255,255,.75); font-size:.72rem; font-weight:700; text-transform:uppercase; }
    .meetings-list-hero .btn-light { border:0; color:var(--ml-primary-dark); font-weight:700; }
    .meetings-list-card { background:#fff; border:1px solid var(--ml-line); border-radius:var(--ml-radius); box-shadow:0 8px 24px rgba(17,47,38,.06); overflow:visible; }
    .meetings-list-card .card-header { background:linear-gradient(90deg,#f9fcfa,#fff); border-bottom-color:var(--ml-line); min-height:66px; padding:1rem 1.25rem; }
    .meetings-list-card .card-title { color:var(--ml-ink); font-size:1rem; font-weight:700; letter-spacing:-.02em; }
    .meetings-list-card thead th { background:#f8fbf9; color:var(--ml-muted); font-size:.68rem; font-weight:700; letter-spacing:.05em; text-transform:uppercase; white-space:nowrap; }
    .meetings-list-card td { border-color:var(--ml-line); padding:.85rem .75rem; vertical-align:middle; }
    .meetings-list-card tbody tr { transition:background .16s ease; }
    .meetings-list-card tbody tr:hover { background:#f8fbf9; }
    .meeting-name { color:var(--ml-ink); font-weight:700; text-decoration:none; }
    .meeting-name:hover { color:var(--ml-primary-dark); text-decoration:underline; }
    .meeting-meta { color:var(--ml-muted); font-size:.78rem; margin-top:.18rem; }
    .attendance-present { background:#e9f7ef; color:#16713f; }
    .attendance-absent { background:#f2f4f3; color:#5c6b64; }
    .meetings-list-card .badge { font-size:.75rem; font-weight:700; min-width:30px; }
    .meetings-list-card .btn { border-radius:9px; font-size:.78rem; font-weight:700; }
    @media (max-width:575.98px) { .meetings-list-premium { border-radius:0; margin:0 -1rem; padding:1rem; } .meetings-list-hero { padding:1.3rem; } .meetings-list-count { margin-top:1rem; } .meetings-list-card .card-header { padding-left:1rem; padding-right:1rem; } }
</style>

<main class="meetings-list-premium">
    <section class="meetings-list-hero" aria-label="<?= gettext('Meeting List') ?>">
        <div class="row align-items-center g-3"><div class="col-lg-8"><div class="meetings-list-kicker"><i class="ti ti-users-group me-1"></i><?= gettext('Meeting List') ?></div><h1 class="meetings-list-title"><?= gettext('All scheduled and past meetings') ?></h1><p class="meetings-list-copy"><?= gettext('Find a meeting quickly, review its attendance, or continue its follow-up.') ?></p><?php if ($canEdit): ?><a href="<?= InputUtils::escapeAttribute($sRootPath) ?>/meetings/editor" class="btn btn-light mt-3"><i class="ti ti-plus me-1"></i><?= gettext('New Meeting') ?></a><?php endif; ?></div><div class="col-lg-4 text-lg-end"><div class="meetings-list-count text-start text-lg-start"><strong><?= count($meetings) ?></strong><span><?= gettext('All meetings') ?></span></div></div></div>
    </section>

    <section class="meetings-list-card" aria-labelledby="all-meetings-title">
        <div class="card-header d-flex align-items-center"><div><h2 class="card-title mb-0" id="all-meetings-title"><?= gettext('All Meetings') ?></h2><div class="meeting-meta"><?= gettext('Attendance is available directly from each meeting.') ?></div></div><?php if ($canEdit): ?><a href="<?= InputUtils::escapeAttribute($sRootPath) ?>/meetings/editor" class="btn btn-outline-primary btn-sm ms-auto"><i class="ti ti-plus me-1"></i><?= gettext('New Meeting') ?></a><?php endif; ?></div>
        <div class="card-body p-0">
            <?php if (empty($meetings)): ?>
                <div class="p-4 text-center text-body-secondary"><i class="ti ti-calendar-off d-block fs-1 mb-2 text-success"></i><?= gettext('No meetings found.') ?></div>
            <?php else: ?>
                <div class="table-responsive"><table class="table table-vcenter mb-0"><thead><tr><th><?= gettext('Name') ?></th><th><?= gettext('Date & Time') ?></th><th><?= gettext('Organizer') ?></th><th class="text-center"><?= gettext('Present') ?></th><th class="text-center"><?= gettext('Absent') ?></th><th class="text-end"></th></tr></thead><tbody>
                    <?php foreach ($meetings as $meeting): ?>
                    <tr><td><a href="<?= InputUtils::escapeAttribute($sRootPath) ?>/meetings/view/<?= (int) $meeting['id'] ?>" class="meeting-name"><?= InputUtils::escapeHTML($meeting['name']) ?></a></td><td><span class="fw-semibold d-block"><?= InputUtils::escapeHTML($meeting['formattedDateTime']) ?></span></td><td><span class="meeting-meta d-block text-truncate" style="max-width:210px"><?= InputUtils::escapeHTML($meeting['organizerLabel']) ?></span></td><td class="text-center"><span class="badge attendance-present"><?= (int) $meeting['presentCount'] ?></span></td><td class="text-center"><span class="badge attendance-absent"><?= (int) $meeting['absentCount'] ?></span></td><td class="text-end text-nowrap"><a href="<?= InputUtils::escapeAttribute($sRootPath) ?>/meetings/view/<?= (int) $meeting['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="ti ti-eye me-1"></i><?= gettext('View') ?></a><?php if ($canEdit): ?><a href="<?= InputUtils::escapeAttribute($sRootPath) ?>/meetings/editor/<?= (int) $meeting['id'] ?>" class="btn btn-sm btn-ghost-secondary"><i class="ti ti-edit me-1"></i><?= gettext('Edit') ?></a><?php endif; ?></td></tr>
                    <?php endforeach; ?>
                </tbody></table></div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require SystemURLs::getDocumentRoot() . '/Include/Footer.php'; ?>
