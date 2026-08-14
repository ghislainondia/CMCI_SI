<?php

use ChurchCRM\dto\ChurchVocabulary;
use ChurchCRM\dto\SystemURLs;
use ChurchCRM\Utils\InputUtils;

require SystemURLs::getDocumentRoot() . '/Include/Header.php';
?>

<style nonce="<?= SystemURLs::getCSPNonce() ?>">
    .meetings-dashboard {
        --mtg-primary: #166c5d;
        --mtg-primary-dark: #0c4d42;
        --mtg-primary-soft: #e8f4f0;
        --mtg-ink: #17251f;
        --mtg-muted: #66756e;
        --mtg-line: #e5ece8;
        --mtg-canvas: #f5f8f6;
        --mtg-radius: 16px;
        --mtg-shadow: 0 8px 24px rgba(17, 47, 38, .06);
        animation: mtg-enter .35s ease-out both;
        background: var(--mtg-canvas);
        border-radius: var(--mtg-radius);
        color: var(--mtg-ink);
        padding: 1.25rem;
    }

    @keyframes mtg-enter { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    .meetings-dashboard a:focus-visible { outline: 3px solid rgba(226, 166, 66, .55); outline-offset: 3px; }
    .mtg-hero { background: linear-gradient(118deg, var(--mtg-primary-dark), var(--mtg-primary) 64%, #258976); border: 0; border-radius: 20px; box-shadow: 0 12px 30px rgba(11, 77, 66, .18); color: #fff; overflow: hidden; position: relative; }
    .mtg-hero::after { border: 1px solid rgba(255,255,255,.14); border-radius: 50%; content: ''; height: 300px; position: absolute; right: -70px; top: -125px; width: 300px; }
    .mtg-hero .card-body { position: relative; z-index: 1; }
    .mtg-kicker { color: rgba(255,255,255,.78); font-size: .73rem; font-weight: 700; letter-spacing: .07em; text-transform: uppercase; }
    .mtg-hero-title { font-size: clamp(1.6rem, 3vw, 2.3rem); font-weight: 700; letter-spacing: -.04em; line-height: 1.1; }
    .mtg-hero-copy { color: rgba(255,255,255,.85); line-height: 1.5; max-width: 590px; }
    .mtg-hero .btn-light { border: 0; color: var(--mtg-primary-dark); font-weight: 700; }
    .mtg-next { backdrop-filter: blur(8px); background: rgba(255,255,255,.13); border: 1px solid rgba(255,255,255,.18); border-radius: 14px; padding: 1rem; }
    .mtg-next-label { color: rgba(255,255,255,.7); font-size: .7rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }
    .mtg-next-name { font-size: .94rem; font-weight: 700; line-height: 1.35; margin-top: .4rem; }
    .mtg-next-time { color: rgba(255,255,255,.8); font-size: .79rem; margin-top: .4rem; }

    .mtg-stat, .mtg-panel { background: #fff; border: 1px solid var(--mtg-line); border-radius: var(--mtg-radius); box-shadow: var(--mtg-shadow); }
    .mtg-stat { display: block; height: 100%; min-height: 124px; padding: 1rem; text-decoration: none; transition: transform .18s ease, box-shadow .18s ease; }
    .mtg-stat:hover { box-shadow: 0 14px 28px rgba(17,47,38,.1); transform: translateY(-2px); }
    .mtg-stat--primary { background: var(--mtg-primary); border-color: var(--mtg-primary); color: #fff; }
    .mtg-stat-label { color: var(--mtg-muted); font-size: .76rem; font-weight: 600; }
    .mtg-stat--primary .mtg-stat-label { color: rgba(255,255,255,.75); }
    .mtg-stat-value { color: var(--mtg-ink); font-size: 1.8rem; font-weight: 700; letter-spacing: -.045em; line-height: 1.1; }
    .mtg-stat--primary .mtg-stat-value { color: #fff; }
    .mtg-stat-icon { align-items: center; background: var(--mtg-primary-soft); border-radius: 11px; color: var(--mtg-primary); display: inline-flex; font-size: 1.15rem; height: 40px; justify-content: center; width: 40px; }
    .mtg-stat--primary .mtg-stat-icon { background: rgba(255,255,255,.15); color: #fff; }
    .mtg-stat-icon--blue { background: #eaf1ff; color: #3764b8; }
    .mtg-stat-icon--gold { background: #fff5df; color: #b77711; }
    .mtg-stat-note { color: var(--mtg-muted); font-size: .76rem; margin-top: .35rem; }
    .mtg-stat--primary .mtg-stat-note { color: rgba(255,255,255,.75); }

    .mtg-panel { height: 100%; overflow: hidden; }
    .mtg-panel-header { align-items: center; border-bottom: 1px solid var(--mtg-line); display: flex; justify-content: space-between; min-height: 68px; padding: 1rem 1.25rem; }
    .mtg-panel-title { font-size: 1rem; font-weight: 700; letter-spacing: -.02em; margin: 0; }
    .mtg-panel-subtitle { color: var(--mtg-muted); font-size: .8rem; margin-top: .15rem; }
    .mtg-link { color: var(--mtg-primary); font-size: .82rem; font-weight: 700; text-decoration: none; white-space: nowrap; }
    .mtg-link:hover { color: var(--mtg-primary-dark); text-decoration: underline; }
    .mtg-meeting-list { padding: .35rem 1.25rem .9rem; }
    .mtg-meeting { align-items: center; border-bottom: 1px solid var(--mtg-line); color: var(--mtg-ink); display: flex; gap: .85rem; min-height: 78px; padding: .8rem 0; text-decoration: none; }
    .mtg-meeting:last-child { border-bottom: 0; }
    .mtg-meeting:hover { color: var(--mtg-primary-dark); text-decoration: none; }
    .mtg-date { align-items: center; background: var(--mtg-primary-soft); border-radius: 10px; color: var(--mtg-primary-dark); display: flex; flex: 0 0 47px; flex-direction: column; justify-content: center; line-height: 1; min-height: 50px; }
    .mtg-date small { font-size: .62rem; font-weight: 700; letter-spacing: .04em; margin-bottom: .16rem; }
    .mtg-date strong { font-size: 1.2rem; }
    .mtg-meeting-name { font-size: .92rem; font-weight: 700; line-height: 1.35; }
    .mtg-meeting-meta { color: var(--mtg-muted); font-size: .78rem; margin-top: .2rem; }
    .mtg-meeting-time { color: var(--mtg-primary); font-size: .78rem; font-weight: 700; margin-left: auto; white-space: nowrap; }
    .mtg-empty { color: var(--mtg-muted); padding: 2.75rem 1.25rem; text-align: center; }
    .mtg-empty .ti { color: var(--mtg-primary); font-size: 2rem; }

    .mtg-actions { padding: 1.25rem; }
    .mtg-action-callout { background: #f0f8f5; border: 1px solid #dbeee8; border-radius: 12px; padding: 1rem; }
    .mtg-action-callout .ti { color: var(--mtg-primary); font-size: 1.2rem; }
    .mtg-action-title { font-size: .9rem; font-weight: 700; margin-top: .5rem; }
    .mtg-action-copy { color: var(--mtg-muted); font-size: .81rem; line-height: 1.45; margin: .2rem 0 0; }
    .mtg-action { align-items: center; border: 1px solid var(--mtg-line); border-radius: 11px; color: var(--mtg-ink); display: flex; font-size: .87rem; font-weight: 600; gap: .7rem; margin-top: .75rem; padding: .77rem .85rem; text-decoration: none; transition: background .18s ease, border-color .18s ease, transform .18s ease; }
    .mtg-action:hover { background: var(--mtg-primary-soft); border-color: #c8e5da; color: var(--mtg-primary-dark); text-decoration: none; transform: translateX(2px); }
    .mtg-action .ti:first-child { color: var(--mtg-primary); font-size: 1.08rem; }
    .mtg-action .ti:last-child { color: var(--mtg-muted); margin-left: auto; }
    .mtg-attendance { align-items: center; display: flex; gap: .7rem; margin-top: 1rem; }
    .mtg-attendance-badge { align-items: center; border-radius: 10px; display: inline-flex; font-size: .78rem; font-weight: 700; gap: .35rem; padding: .45rem .55rem; }
    .mtg-attendance-badge--present { background: #e9f7ef; color: #16713f; }
    .mtg-attendance-badge--absent { background: #f2f4f3; color: #5c6b64; }
    .mtg-recent-grid { display: grid; gap: .75rem; grid-template-columns: repeat(2, minmax(0, 1fr)); padding: 1.25rem; }
    .mtg-recent { border: 1px solid var(--mtg-line); border-radius: 12px; color: var(--mtg-ink); display: block; padding: 1rem; text-decoration: none; transition: background .18s ease, border-color .18s ease, transform .18s ease; }
    .mtg-recent:hover { background: #f8fbf9; border-color: #c8e5da; color: var(--mtg-primary-dark); text-decoration: none; transform: translateY(-2px); }
    .mtg-recent-title { font-size: .9rem; font-weight: 700; line-height: 1.35; }
    .mtg-recent-meta { color: var(--mtg-muted); font-size: .77rem; line-height: 1.45; margin-top: .45rem; }

    @media (max-width: 575.98px) {
        .meetings-dashboard { border-radius: 0; margin: 0 -1rem; padding: 1rem; }
        .mtg-hero .card-body { padding: 1.4rem !important; }
        .mtg-next { margin-top: .5rem; }
        .mtg-stat { min-height: 112px; padding: .85rem; }
        .mtg-stat-value { font-size: 1.5rem; }
        .mtg-stat-icon { height: 35px; width: 35px; }
        .mtg-panel-header, .mtg-meeting-list { padding-left: 1rem; padding-right: 1rem; }
        .mtg-meeting-time { display: none; }
        .mtg-recent-grid { grid-template-columns: 1fr; padding: 1rem; }
    }
</style>

<main class="meetings-dashboard">
    <section class="card mtg-hero mb-3" aria-label="<?= ChurchVocabulary::meetings() ?>">
        <div class="card-body p-4 p-md-5">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="mtg-kicker mb-3"><i class="ti ti-users-group me-1"></i><?= ChurchVocabulary::meetings() ?></div>
                    <h1 class="mtg-hero-title mb-2"><?= gettext('Manage church meetings and attendance') ?></h1>
                    <p class="mtg-hero-copy mb-4"><?= gettext('Plan, organize, and follow up on every meeting from one place.') ?></p>
                    <?php if ($canEdit): ?>
                        <a href="<?= InputUtils::escapeAttribute($sRootPath) ?>/meetings/editor" class="btn btn-light"><i class="ti ti-plus me-1"></i><?= gettext('New Meeting') ?></a>
                    <?php endif; ?>
                    <a href="<?= InputUtils::escapeAttribute($sRootPath) ?>/meetings/list" class="btn btn-outline-light ms-2"><i class="ti ti-list me-1"></i><?= gettext('Meeting List') ?></a>
                </div>
                <div class="col-lg-4">
                    <div class="mtg-next">
                        <div class="mtg-next-label"><?= gettext('Next meeting') ?></div>
                        <?php if ($nextMeeting !== null): ?>
                            <div class="mtg-next-name"><?= InputUtils::escapeHTML($nextMeeting['name']) ?></div>
                            <div class="mtg-next-time"><i class="ti ti-calendar-event me-1"></i><?= InputUtils::escapeHTML($nextMeeting['formattedDateTime']) ?></div>
                        <?php else: ?>
                            <div class="mtg-next-name"><?= gettext('No upcoming meetings.') ?></div>
                            <?php if ($canEdit): ?><div class="mtg-next-time"><?= gettext('Create the next meeting when you are ready.') ?></div><?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="row g-3 mb-3" aria-label="<?= gettext('Meeting overview') ?>">
        <div class="col-6 col-lg-3"><a href="<?= InputUtils::escapeAttribute($sRootPath) ?>/meetings/list" class="mtg-stat mtg-stat--primary"><div class="d-flex justify-content-between align-items-start mb-3"><span class="mtg-stat-label"><?= ChurchVocabulary::meetings() ?></span><span class="mtg-stat-icon"><i class="ti ti-users-group"></i></span></div><div class="mtg-stat-value"><?= (int) $totalCount ?></div><div class="mtg-stat-note"><?= gettext('All meetings') ?></div></a></div>
        <div class="col-6 col-lg-3"><div class="mtg-stat"><div class="d-flex justify-content-between align-items-start mb-3"><span class="mtg-stat-label"><?= gettext('Upcoming Meetings') ?></span><span class="mtg-stat-icon"><i class="ti ti-calendar-event"></i></span></div><div class="mtg-stat-value"><?= (int) $upcomingCount ?></div><div class="mtg-stat-note"><?= gettext('Scheduled') ?></div></div></div>
        <div class="col-6 col-lg-3"><div class="mtg-stat"><div class="d-flex justify-content-between align-items-start mb-3"><span class="mtg-stat-label"><?= gettext('Recent Meetings') ?></span><span class="mtg-stat-icon mtg-stat-icon--blue"><i class="ti ti-history"></i></span></div><div class="mtg-stat-value"><?= (int) $pastCount ?></div><div class="mtg-stat-note"><?= gettext('Completed') ?></div></div></div>
        <div class="col-6 col-lg-3"><div class="mtg-stat"><div class="d-flex justify-content-between align-items-start mb-3"><span class="mtg-stat-label"><?= gettext('Latest attendance') ?></span><span class="mtg-stat-icon mtg-stat-icon--gold"><i class="ti ti-user-check"></i></span></div><div class="mtg-stat-value"><?= (int) $lastAttendance['present'] ?></div><div class="mtg-stat-note"><?= gettext('Present at the last meeting') ?></div></div></div>
    </section>

    <div class="row g-3 mb-3">
        <section class="col-lg-8" aria-labelledby="upcoming-meetings-title">
            <div class="mtg-panel">
                <div class="mtg-panel-header"><div><h2 class="mtg-panel-title" id="upcoming-meetings-title"><?= gettext('Upcoming Meetings') ?></h2><div class="mtg-panel-subtitle"><?= gettext('Your next scheduled meetings') ?></div></div><a href="<?= InputUtils::escapeAttribute($sRootPath) ?>/meetings/list" class="mtg-link"><?= gettext('View All') ?> <i class="ti ti-arrow-right"></i></a></div>
                <?php if ($upcomingMeetings === []): ?>
                    <div class="mtg-empty"><i class="ti ti-calendar-off d-block mb-2"></i><strong class="d-block text-dark mb-1"><?= gettext('No upcoming meetings.') ?></strong><span><?= gettext('Your future meetings will appear here.') ?></span></div>
                <?php else: ?>
                    <div class="mtg-meeting-list">
                        <?php foreach (array_slice($upcomingMeetings, 0, 4) as $meeting): ?>
                            <a href="<?= InputUtils::escapeAttribute($sRootPath) ?>/meetings/view/<?= (int) $meeting['id'] ?>" class="mtg-meeting">
                                <span class="mtg-date"><small><?= InputUtils::escapeHTML($meeting['monthNumber']) ?></small><strong><?= InputUtils::escapeHTML($meeting['dayNumber']) ?></strong></span>
                                <span class="min-w-0"><span class="mtg-meeting-name d-block text-truncate"><?= InputUtils::escapeHTML($meeting['name']) ?></span><span class="mtg-meeting-meta d-block text-truncate"><i class="ti ti-building-community me-1"></i><?= InputUtils::escapeHTML($meeting['organizerLabel']) ?></span></span>
                                <span class="mtg-meeting-time"><i class="ti ti-clock me-1"></i><?= InputUtils::escapeHTML($meeting['timeDisplay']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <aside class="col-lg-4" aria-labelledby="meeting-actions-title">
            <div class="mtg-panel"><div class="mtg-panel-header"><div><h2 class="mtg-panel-title" id="meeting-actions-title"><?= gettext('Quick access') ?></h2><div class="mtg-panel-subtitle"><?= gettext('Your most useful actions') ?></div></div></div><div class="mtg-actions"><div class="mtg-action-callout"><i class="ti ti-clipboard-check"></i><div class="mtg-action-title"><?= gettext('Attendance overview') ?></div><p class="mtg-action-copy"><?= gettext('The last recorded meeting had the attendance shown below.') ?></p><div class="mtg-attendance"><span class="mtg-attendance-badge mtg-attendance-badge--present"><i class="ti ti-check"></i><?= (int) $lastAttendance['present'] ?> <?= gettext('Present') ?></span><span class="mtg-attendance-badge mtg-attendance-badge--absent"><i class="ti ti-minus"></i><?= (int) $lastAttendance['absent'] ?> <?= gettext('Absent') ?></span></div></div><?php if ($canEdit): ?><a href="<?= InputUtils::escapeAttribute($sRootPath) ?>/meetings/editor" class="mtg-action"><i class="ti ti-plus"></i><?= gettext('New Meeting') ?><i class="ti ti-chevron-right"></i></a><?php endif; ?><a href="<?= InputUtils::escapeAttribute($sRootPath) ?>/meetings/list" class="mtg-action"><i class="ti ti-list"></i><?= gettext('Meeting List') ?><i class="ti ti-chevron-right"></i></a></div></div>
        </aside>
    </div>

    <section class="mtg-panel" aria-labelledby="recent-meetings-title">
        <div class="mtg-panel-header"><div><h2 class="mtg-panel-title" id="recent-meetings-title"><?= gettext('Recent Meetings') ?></h2><div class="mtg-panel-subtitle"><?= gettext('Latest completed meetings') ?></div></div><a href="<?= InputUtils::escapeAttribute($sRootPath) ?>/meetings/list" class="mtg-link"><?= gettext('View All') ?> <i class="ti ti-arrow-right"></i></a></div>
        <?php if ($pastMeetings === []): ?>
            <div class="mtg-empty"><i class="ti ti-history-off d-block mb-2"></i><span><?= gettext('No past meetings yet.') ?></span></div>
        <?php else: ?>
            <div class="mtg-recent-grid">
                <?php foreach (array_slice($pastMeetings, 0, 4) as $meeting): ?>
                    <a href="<?= InputUtils::escapeAttribute($sRootPath) ?>/meetings/view/<?= (int) $meeting['id'] ?>" class="mtg-recent"><div class="mtg-recent-title"><?= InputUtils::escapeHTML($meeting['name']) ?></div><div class="mtg-recent-meta"><i class="ti ti-calendar me-1"></i><?= InputUtils::escapeHTML($meeting['formattedDateTime']) ?><br><i class="ti ti-building-community me-1"></i><?= InputUtils::escapeHTML($meeting['organizerLabel']) ?></div></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require SystemURLs::getDocumentRoot() . '/Include/Footer.php'; ?>
