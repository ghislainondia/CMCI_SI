<?php

use ChurchCRM\dto\SystemURLs;
use ChurchCRM\Utils\InputUtils;

require SystemURLs::getDocumentRoot() . '/Include/Header.php';
?>

<style nonce="<?= SystemURLs::getCSPNonce() ?>">
    /* Deliberately scoped to the leader dashboard; the surrounding ChurchCRM shell remains isolated. */
    .adm-dashboard {
        --adm-primary: #166c5d;
        --adm-primary-dark: #0c4d42;
        --adm-primary-soft: #e8f4f0;
        --adm-accent: #e2a642;
        --adm-ink: #17251f;
        --adm-muted: #66756e;
        --adm-line: #e5ece8;
        --adm-canvas: #f5f8f6;
        --adm-surface: #fff;
        --adm-radius: 16px;
        --adm-shadow: 0 8px 24px rgba(17, 47, 38, .06);
        animation: adm-enter .35s ease-out both;
        background: var(--adm-canvas);
        border-radius: var(--adm-radius);
        color: var(--adm-ink);
        padding: 1.25rem;
    }

    @keyframes adm-enter {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .adm-dashboard a:focus-visible,
    .adm-dashboard button:focus-visible {
        outline: 3px solid rgba(226, 166, 66, .55);
        outline-offset: 3px;
    }

    .adm-hero {
        background: linear-gradient(118deg, var(--adm-primary-dark), var(--adm-primary) 62%, #258976);
        border: 0;
        border-radius: calc(var(--adm-radius) + 4px);
        box-shadow: 0 12px 30px rgba(11, 77, 66, .18);
        color: #fff;
        overflow: hidden;
        position: relative;
    }

    .adm-hero::before,
    .adm-hero::after {
        border: 1px solid rgba(255, 255, 255, .14);
        border-radius: 50%;
        content: '';
        pointer-events: none;
        position: absolute;
    }

    .adm-hero::before { height: 300px; right: -85px; top: -150px; width: 300px; }
    .adm-hero::after { height: 210px; right: 55px; top: -90px; width: 210px; }
    .adm-hero .card-body { min-height: 202px; position: relative; z-index: 1; }
    .adm-hero .carousel-item { min-height: 158px; }
    .adm-hero-kicker {
        align-items: center;
        color: rgba(255, 255, 255, .82);
        display: inline-flex;
        font-size: .75rem;
        font-weight: 700;
        gap: .45rem;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .adm-hero-title { font-size: clamp(1.65rem, 3vw, 2.35rem); font-weight: 700; letter-spacing: -.04em; line-height: 1.08; }
    .adm-hero-copy { color: rgba(255, 255, 255, .86); font-size: 1rem; line-height: 1.55; max-width: 650px; }
    .adm-hero .btn-light { border: 0; color: var(--adm-primary-dark); font-weight: 700; }
    .adm-hero .btn-light:hover { background: #fff; box-shadow: 0 4px 12px rgba(0, 0, 0, .12); color: var(--adm-primary-dark); transform: translateY(-1px); }
    .adm-hero .carousel-indicators { bottom: -1.1rem; }
    .adm-hero .carousel-indicators [data-bs-target] { background: rgba(255, 255, 255, .48); border: 0; border-radius: 50px; height: 5px; opacity: 1; width: 20px; }
    .adm-hero .carousel-indicators .active { background: #fff; width: 34px; }

    .adm-stat-card,
    .adm-panel {
        background: var(--adm-surface);
        border: 1px solid var(--adm-line);
        border-radius: var(--adm-radius);
        box-shadow: var(--adm-shadow);
    }

    .adm-stat-card { height: 100%; min-height: 126px; padding: 1rem; transition: transform .18s ease, box-shadow .18s ease; }
    .adm-stat-card:hover { box-shadow: 0 14px 28px rgba(17, 47, 38, .1); transform: translateY(-2px); }
    .adm-stat-card--primary { background: var(--adm-primary); border-color: var(--adm-primary); color: #fff; }
    .adm-stat-label { color: var(--adm-muted); font-size: .76rem; font-weight: 600; }
    .adm-stat-card--primary .adm-stat-label { color: rgba(255, 255, 255, .75); }
    .adm-stat-value { color: var(--adm-ink); font-size: 1.8rem; font-weight: 700; letter-spacing: -.045em; line-height: 1.08; }
    .adm-stat-card--primary .adm-stat-value { color: #fff; }
    .adm-stat-value--name { font-size: 1rem; letter-spacing: -.02em; line-height: 1.25; }
    .adm-stat-icon {
        align-items: center;
        background: var(--adm-primary-soft);
        border-radius: 11px;
        color: var(--adm-primary);
        display: inline-flex;
        font-size: 1.2rem;
        height: 40px;
        justify-content: center;
        width: 40px;
    }

    .adm-stat-card--primary .adm-stat-icon { background: rgba(255, 255, 255, .15); color: #fff; }
    .adm-stat-icon--gold { background: #fff5df; color: #b77711; }
    .adm-stat-icon--blue { background: #eaf1ff; color: #3764b8; }
    .adm-stat-link { color: inherit; display: block; text-decoration: none; }
    .adm-stat-link:hover { color: inherit; text-decoration: none; }

    .adm-panel { height: 100%; overflow: hidden; }
    .adm-panel-header {
        align-items: center;
        border-bottom: 1px solid var(--adm-line);
        display: flex;
        justify-content: space-between;
        min-height: 68px;
        padding: 1rem 1.25rem;
    }

    .adm-panel-title { font-size: 1rem; font-weight: 700; letter-spacing: -.02em; margin: 0; }
    .adm-panel-subtitle { color: var(--adm-muted); font-size: .8rem; margin-top: .15rem; }
    .adm-link { color: var(--adm-primary); font-size: .82rem; font-weight: 700; text-decoration: none; white-space: nowrap; }
    .adm-link:hover { color: var(--adm-primary-dark); text-decoration: underline; }

    .adm-program-list { margin: 0; padding: .35rem 1.25rem .9rem; }
    .adm-program {
        align-items: center;
        border-bottom: 1px solid var(--adm-line);
        display: flex;
        gap: .9rem;
        min-height: 78px;
        padding: .8rem 0;
    }

    .adm-program:last-child { border-bottom: 0; }
    .adm-date-box {
        align-items: center;
        background: var(--adm-primary-soft);
        border-radius: 10px;
        color: var(--adm-primary-dark);
        display: flex;
        flex: 0 0 47px;
        flex-direction: column;
        font-weight: 700;
        justify-content: center;
        line-height: 1;
        min-height: 50px;
    }

    .adm-date-box small { font-size: .62rem; letter-spacing: .04em; margin-bottom: .16rem; text-transform: uppercase; }
    .adm-date-box strong { font-size: 1.2rem; }
    .adm-program-name { font-size: .92rem; font-weight: 700; line-height: 1.35; }
    .adm-program-meta { color: var(--adm-muted); font-size: .79rem; margin-top: .2rem; }
    .adm-program-time { color: var(--adm-primary); font-size: .78rem; font-weight: 700; margin-left: auto; white-space: nowrap; }

    .adm-empty { color: var(--adm-muted); padding: 2.75rem 1.25rem; text-align: center; }
    .adm-empty .ti { color: var(--adm-primary); font-size: 2rem; }

    .adm-summary { padding: 1.25rem; }
    .adm-summary-callout { background: #f0f8f5; border: 1px solid #dbeee8; border-radius: 12px; padding: 1rem; }
    .adm-summary-callout .ti { color: var(--adm-primary); font-size: 1.2rem; }
    .adm-summary-title { font-size: .9rem; font-weight: 700; margin-top: .55rem; }
    .adm-summary-copy { color: var(--adm-muted); font-size: .82rem; line-height: 1.45; margin: .25rem 0 0; }
    .adm-quick-action {
        align-items: center;
        border: 1px solid var(--adm-line);
        border-radius: 11px;
        color: var(--adm-ink);
        display: flex;
        font-size: .87rem;
        font-weight: 600;
        gap: .7rem;
        margin-top: .75rem;
        padding: .77rem .85rem;
        text-decoration: none;
        transition: background .18s ease, border-color .18s ease, transform .18s ease;
    }

    .adm-quick-action:hover { background: var(--adm-primary-soft); border-color: #c8e5da; color: var(--adm-primary-dark); text-decoration: none; transform: translateX(2px); }
    .adm-quick-action .ti:first-child { color: var(--adm-primary); font-size: 1.1rem; }
    .adm-quick-action .ti:last-child { color: var(--adm-muted); margin-left: auto; }

    .adm-profile-list { padding: .35rem 1.25rem .8rem; }
    .adm-profile {
        align-items: center;
        border-bottom: 1px solid var(--adm-line);
        color: var(--adm-ink);
        display: flex;
        gap: .75rem;
        min-height: 67px;
        padding: .65rem 0;
        text-decoration: none;
        transition: background .16s ease;
    }

    .adm-profile:last-child { border-bottom: 0; }
    .adm-profile:hover { color: var(--adm-primary-dark); text-decoration: none; }
    .adm-avatar,
    .adm-avatar-fallback {
        align-items: center;
        border-radius: 50%;
        flex: 0 0 40px;
        height: 40px;
        justify-content: center;
        object-fit: cover;
        width: 40px;
    }

    .adm-avatar-fallback { background: var(--adm-primary-soft); color: var(--adm-primary); display: inline-flex; font-size: .78rem; font-weight: 700; }
    .adm-profile-name { font-size: .88rem; font-weight: 700; line-height: 1.25; }
    .adm-profile-meta { color: var(--adm-muted); font-size: .76rem; margin-top: .15rem; }
    .adm-profile-arrow { color: #aab7b1; margin-left: auto; }

    @media (max-width: 575.98px) {
        .adm-dashboard { border-radius: 0; margin: 0 -1rem; padding: 1rem; }
        .adm-hero .card-body { min-height: 235px; padding: 1.4rem !important; }
        .adm-hero-copy { font-size: .91rem; }
        .adm-stat-card { min-height: 112px; padding: .85rem; }
        .adm-stat-value { font-size: 1.5rem; }
        .adm-stat-value--name { font-size: .89rem; }
        .adm-stat-icon { flex: 0 0 35px; height: 35px; width: 35px; }
        .adm-program-list, .adm-profile-list { padding-left: 1rem; padding-right: 1rem; }
        .adm-panel-header { padding-left: 1rem; padding-right: 1rem; }
        .adm-program-time { display: none; }
    }
</style>

<main class="adm-dashboard">
    <section class="card adm-hero mb-3" aria-label="<?= gettext('Church information') ?>">
        <div class="card-body p-4 p-md-5">
            <div id="admChurchInformation" class="carousel slide" data-bs-ride="carousel" data-bs-interval="7000">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <div class="adm-hero-kicker mb-3"><i class="ti ti-sparkles"></i><?= gettext('Church information') ?></div>
                        <h1 class="adm-hero-title mb-2"><?= InputUtils::escapeHTML($churchName) ?></h1>
                        <p class="adm-hero-copy mb-4"><?= sprintf(gettext('Welcome, leader of %s. Follow your members and the life of the church from one place.'), InputUtils::escapeHTML($assemblyName)) ?></p>
                        <a href="<?= InputUtils::escapeAttribute($familyUrl) ?>" class="btn btn-light">
                            <i class="ti ti-building-community me-1"></i><?= gettext('My House Assembly') ?>
                        </a>
                    </div>
                    <div class="carousel-item">
                        <div class="adm-hero-kicker mb-3"><i class="ti ti-calendar-week"></i><?= gettext('This week') ?></div>
                        <h2 class="adm-hero-title mb-2"><?= gettext('Stay connected to church life') ?></h2>
                        <p class="adm-hero-copy mb-4"><?= gettext('This banner is ready to display church announcements, images, and important messages.') ?></p>
                        <a href="<?= InputUtils::escapeAttribute($calendarUrl) ?>" class="btn btn-light">
                            <i class="ti ti-calendar-event me-1"></i><?= gettext('View calendar') ?>
                        </a>
                    </div>
                </div>
                <div class="carousel-indicators justify-content-start mx-0">
                    <button type="button" data-bs-target="#admChurchInformation" data-bs-slide-to="0" class="active" aria-current="true" aria-label="<?= gettext('Church information') ?>"></button>
                    <button type="button" data-bs-target="#admChurchInformation" data-bs-slide-to="1" aria-label="<?= gettext('This week') ?>"></button>
                </div>
            </div>
        </div>
    </section>

    <section class="row g-3 mb-3" aria-label="<?= gettext('Dashboard') ?>">
        <div class="col-6 col-lg-3">
            <div class="adm-stat-card adm-stat-card--primary">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <span class="adm-stat-label"><?= gettext('Members') ?></span>
                    <span class="adm-stat-icon"><i class="ti ti-users"></i></span>
                </div>
                <div class="adm-stat-value"><?= (int) $memberCount ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="adm-stat-card">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <span class="adm-stat-label"><?= gettext('Upcoming programs') ?></span>
                    <span class="adm-stat-icon adm-stat-icon--gold"><i class="ti ti-calendar-event"></i></span>
                </div>
                <div class="adm-stat-value"><?= count($upcomingPrograms) ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <a class="adm-stat-link" href="<?= InputUtils::escapeAttribute($familyUrl) ?>">
                <div class="adm-stat-card">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <span class="adm-stat-label"><?= gettext('House Assembly') ?></span>
                        <span class="adm-stat-icon"><i class="ti ti-home-heart"></i></span>
                    </div>
                    <div class="adm-stat-value adm-stat-value--name text-truncate"><?= InputUtils::escapeHTML($assemblyName) ?></div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a class="adm-stat-link" href="<?= InputUtils::escapeAttribute($meetingsUrl) ?>">
                <div class="adm-stat-card">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <span class="adm-stat-label"><?= gettext('Meetings') ?></span>
                        <span class="adm-stat-icon adm-stat-icon--blue"><i class="ti ti-users-group"></i></span>
                    </div>
                    <div class="adm-stat-value adm-stat-value--name"><?= gettext('Open') ?> <i class="ti ti-arrow-up-right"></i></div>
                </div>
            </a>
        </div>
    </section>

    <div class="row g-3 mb-3">
        <section class="col-lg-8" aria-labelledby="adm-programs-title">
            <div class="adm-panel">
                <div class="adm-panel-header">
                    <div>
                        <h2 class="adm-panel-title" id="adm-programs-title"><?= gettext('Upcoming Programs') ?></h2>
                        <div class="adm-panel-subtitle"><?= gettext('The next seven days') ?></div>
                    </div>
                    <a href="<?= InputUtils::escapeAttribute($calendarUrl) ?>" class="adm-link"><?= gettext('View calendar') ?> <i class="ti ti-arrow-right"></i></a>
                </div>
                <?php if ($upcomingPrograms === []): ?>
                    <div class="adm-empty">
                        <i class="ti ti-calendar-off d-block mb-2"></i>
                        <strong class="d-block text-dark mb-1"><?= gettext('No upcoming programs this week') ?></strong>
                        <span><?= gettext('Church programs scheduled for the next seven days will appear here.') ?></span>
                    </div>
                <?php else: ?>
                    <div class="adm-program-list">
                        <?php foreach ($upcomingPrograms as $program): ?>
                            <div class="adm-program">
                                <div class="adm-date-box" aria-label="<?= InputUtils::escapeAttribute($program['when']) ?>">
                                    <small><?= InputUtils::escapeHTML($program['monthShort']) ?></small>
                                    <strong><?= InputUtils::escapeHTML($program['dayNum']) ?></strong>
                                </div>
                                <div class="min-w-0">
                                    <div class="adm-program-name"><?= InputUtils::escapeHTML($program['title']) ?></div>
                                    <div class="adm-program-meta"><i class="ti ti-clock me-1"></i><?= InputUtils::escapeHTML($program['dayShort']) ?> · <?= InputUtils::escapeHTML($program['timeStart']) ?> – <?= InputUtils::escapeHTML($program['timeEnd']) ?></div>
                                </div>
                                <span class="adm-program-time"><?= gettext('Program') ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <aside class="col-lg-4" aria-labelledby="adm-access-title">
            <div class="adm-panel">
                <div class="adm-panel-header">
                    <div>
                        <h2 class="adm-panel-title" id="adm-access-title"><?= gettext('Quick access') ?></h2>
                        <div class="adm-panel-subtitle"><?= gettext('Your most useful actions') ?></div>
                    </div>
                </div>
                <div class="adm-summary">
                    <div class="adm-summary-callout">
                        <i class="ti ti-heart-handshake"></i>
                        <div class="adm-summary-title"><?= gettext('Your House Assembly') ?></div>
                        <p class="adm-summary-copy"><?= sprintf(gettext('%s members are currently assigned to your assembly.'), (int) $memberCount) ?></p>
                    </div>
                    <a href="<?= InputUtils::escapeAttribute($familyUrl) ?>" class="adm-quick-action"><i class="ti ti-building-community"></i><?= gettext('My House Assembly') ?><i class="ti ti-chevron-right"></i></a>
                    <a href="<?= InputUtils::escapeAttribute($meetingsUrl) ?>" class="adm-quick-action"><i class="ti ti-users-group"></i><?= gettext('Meetings') ?><i class="ti ti-chevron-right"></i></a>
                    <a href="<?= InputUtils::escapeAttribute($calendarUrl) ?>" class="adm-quick-action"><i class="ti ti-calendar"></i><?= gettext('Calendar') ?><i class="ti ti-chevron-right"></i></a>
                </div>
            </div>
        </aside>
    </div>

    <section class="adm-panel" aria-labelledby="adm-profiles-title">
        <div class="adm-panel-header">
            <div>
                <h2 class="adm-panel-title" id="adm-profiles-title"><?= gettext('Recent Profiles') ?></h2>
                <div class="adm-panel-subtitle"><?= gettext('Members recently added or updated') ?></div>
            </div>
            <a href="<?= InputUtils::escapeAttribute($familyUrl) ?>" class="adm-link"><?= gettext('View all') ?> <i class="ti ti-arrow-right"></i></a>
        </div>
        <?php if ($recentProfiles === []): ?>
            <div class="adm-empty">
                <i class="ti ti-users-minus d-block mb-2"></i>
                <span><?= gettext('No members are assigned to this house assembly yet.') ?></span>
            </div>
        <?php else: ?>
            <div class="adm-profile-list">
                <?php foreach ($recentProfiles as $profile): ?>
                    <a href="<?= InputUtils::escapeAttribute($sRootPath . '/people/view/' . $profile['id']) ?>" class="adm-profile">
                        <?php if ($profile['hasPhoto']): ?>
                            <img class="adm-avatar" src="<?= InputUtils::escapeAttribute($profile['photoUrl']) ?>" alt="<?= InputUtils::escapeAttribute($profile['name']) ?>" loading="lazy" width="40" height="40">
                        <?php else: ?>
                            <span class="adm-avatar-fallback" aria-hidden="true"><?= InputUtils::escapeHTML($profile['initials']) ?></span>
                        <?php endif; ?>
                        <span class="min-w-0">
                            <span class="adm-profile-name d-block text-truncate"><?= InputUtils::escapeHTML($profile['name']) ?></span>
                            <span class="adm-profile-meta d-block"><?= $profile['updatedAt'] !== '' ? sprintf(gettext('Updated on %s'), InputUtils::escapeHTML($profile['updatedAt'])) : gettext('Member profile') ?></span>
                        </span>
                        <i class="ti ti-chevron-right adm-profile-arrow"></i>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php
require SystemURLs::getDocumentRoot() . '/Include/Footer.php';
