<?php

use ChurchCRM\dto\SystemURLs;
use ChurchCRM\Utils\InputUtils;

require SystemURLs::getDocumentRoot() . '/Include/Header.php';
?>

<style nonce="<?= SystemURLs::getCSPNonce() ?>">
    .adm-hero { background: linear-gradient(118deg, #7c3aed 0%, #4f46e5 52%, #1677ff 100%); border: 0; color: #fff; overflow: hidden; }
    .adm-hero::after { background: radial-gradient(circle, rgba(255,255,255,.35) 0 9%, transparent 10% 18%, rgba(255,255,255,.15) 19% 28%, transparent 29%); content: ""; height: 240px; opacity: .8; position: absolute; right: -35px; top: -48px; width: 240px; }
    .adm-hero .card-body { min-height: 210px; position: relative; z-index: 1; }
    .adm-hero .carousel-item { min-height: 170px; }
    .adm-hero .btn-light { color: #4f46e5; }
    .adm-program-icon { align-items: center; background: var(--tblr-primary-lt); border-radius: 10px; color: var(--tblr-primary); display: inline-flex; height: 40px; justify-content: center; width: 40px; }
    .adm-profile-avatar { background-position: center; background-size: cover; }
</style>

<div class="card adm-hero mb-3 position-relative">
    <div class="card-body p-4 p-md-5">
        <div id="admChurchInformation" class="carousel slide" data-bs-ride="carousel" data-bs-interval="7000">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <span class="badge bg-white-lt text-white mb-3"><?= gettext('Church information') ?></span>
                    <h3 class="display-6 fw-bold mb-2"><?= InputUtils::escapeHTML($churchName) ?></h3>
                    <p class="fs-3 mb-4 col-xl-7"><?= sprintf(gettext('Welcome, leader of %s. Follow your members and the life of the church from one place.'), InputUtils::escapeHTML($assemblyName)) ?></p>
                    <a href="<?= InputUtils::escapeAttribute($familyUrl) ?>" class="btn btn-light"><i class="ti ti-building-community me-1"></i><?= gettext('My House Assembly') ?></a>
                </div>
                <div class="carousel-item">
                    <span class="badge bg-white-lt text-white mb-3"><?= gettext('This week') ?></span>
                    <h3 class="display-6 fw-bold mb-2"><?= gettext('Stay connected to church life') ?></h3>
                    <p class="fs-3 mb-4 col-xl-7"><?= gettext('This banner is ready to display church announcements, images, and important messages.') ?></p>
                    <a href="<?= InputUtils::escapeAttribute($calendarUrl) ?>" class="btn btn-light"><i class="ti ti-calendar-event me-1"></i><?= gettext('View calendar') ?></a>
                </div>
            </div>
            <div class="carousel-indicators justify-content-start mx-0 mb-0">
                <button type="button" data-bs-target="#admChurchInformation" data-bs-slide-to="0" class="active" aria-current="true" aria-label="<?= gettext('Church information') ?>"></button>
                <button type="button" data-bs-target="#admChurchInformation" data-bs-slide-to="1" aria-label="<?= gettext('This week') ?>"></button>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-6 col-lg-3 mb-3"><div class="card card-sm card-status-top bg-primary"><div class="card-body"><div class="text-secondary"><?= gettext('Members') ?></div><div class="h1 mb-0"><?= (int) $memberCount ?></div></div></div></div>
    <div class="col-6 col-lg-3 mb-3"><div class="card card-sm card-status-top bg-purple"><div class="card-body"><div class="text-secondary"><?= gettext('Upcoming programs') ?></div><div class="h1 mb-0"><?= count($upcomingPrograms) ?></div></div></div></div>
    <div class="col-6 col-lg-3 mb-3"><a class="card card-sm card-status-top bg-blue text-reset text-decoration-none" href="<?= InputUtils::escapeAttribute($familyUrl) ?>"><div class="card-body"><div class="text-secondary"><?= gettext('House Assembly') ?></div><div class="fw-bold text-truncate"><?= InputUtils::escapeHTML($assemblyName) ?></div></div></a></div>
    <div class="col-6 col-lg-3 mb-3"><a class="card card-sm card-status-top bg-teal text-reset text-decoration-none" href="<?= InputUtils::escapeAttribute($meetingsUrl) ?>"><div class="card-body"><div class="text-secondary"><?= gettext('Meetings') ?></div><div class="fw-bold"><i class="ti ti-arrow-up-right me-1"></i><?= gettext('Open') ?></div></div></a></div>
</div>

<div class="row">
    <div class="col-lg-8 mb-3">
        <div class="card h-100">
            <div class="card-header"><h3 class="card-title"><?= gettext('Upcoming Programs') ?></h3><div class="card-actions"><a href="<?= InputUtils::escapeAttribute($calendarUrl) ?>" class="btn btn-ghost-primary btn-sm"><?= gettext('View calendar') ?></a></div></div>
            <div class="card-body">
                <?php if ($upcomingPrograms === []): ?>
                <div class="empty"><div class="empty-icon"><i class="ti ti-calendar-off"></i></div><p class="empty-title"><?= gettext('No upcoming programs this week') ?></p><p class="empty-subtitle text-secondary"><?= gettext('Church programs scheduled for the next seven days will appear here.') ?></p></div>
                <?php else: ?>
                <div class="row">
                    <?php foreach ($upcomingPrograms as $program): ?>
                    <div class="col-md-4 mb-3 mb-md-0"><div class="border rounded p-3 h-100"><div class="adm-program-icon mb-3"><i class="ti ti-calendar-event"></i></div><div class="fw-bold mb-1"><?= InputUtils::escapeHTML($program['title']) ?></div><div class="text-secondary small"><?= InputUtils::escapeHTML($program['when']) ?> – <?= InputUtils::escapeHTML($program['end']) ?></div></div></div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-3">
        <div class="card h-100">
            <div class="card-header"><h3 class="card-title"><?= gettext('Quick access') ?></h3></div>
            <div class="list-group list-group-flush">
                <a href="<?= InputUtils::escapeAttribute($familyUrl) ?>" class="list-group-item list-group-item-action d-flex align-items-center"><span class="avatar avatar-sm bg-primary-lt text-primary me-3"><i class="ti ti-building-community"></i></span><span class="flex-fill"><span class="fw-medium d-block"><?= gettext('My House Assembly') ?></span><span class="text-secondary small"><?= gettext('View members and details') ?></span></span><i class="ti ti-chevron-right"></i></a>
                <a href="<?= InputUtils::escapeAttribute($meetingsUrl) ?>" class="list-group-item list-group-item-action d-flex align-items-center"><span class="avatar avatar-sm bg-teal-lt text-teal me-3"><i class="ti ti-users-group"></i></span><span class="flex-fill"><span class="fw-medium d-block"><?= gettext('Meetings') ?></span><span class="text-secondary small"><?= gettext('View meetings and follow-up') ?></span></span><i class="ti ti-chevron-right"></i></a>
                <a href="<?= InputUtils::escapeAttribute($calendarUrl) ?>" class="list-group-item list-group-item-action d-flex align-items-center"><span class="avatar avatar-sm bg-purple-lt text-purple me-3"><i class="ti ti-calendar"></i></span><span class="flex-fill"><span class="fw-medium d-block"><?= gettext('Calendar') ?></span><span class="text-secondary small"><?= gettext('Church programs') ?></span></span><i class="ti ti-chevron-right"></i></a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title"><?= gettext('Recent Profiles') ?></h3><div class="card-actions"><a href="<?= InputUtils::escapeAttribute($familyUrl) ?>" class="btn btn-ghost-primary btn-sm"><?= gettext('My House Assembly') ?></a></div></div>
    <div class="list-group list-group-flush">
        <?php if ($recentProfiles === []): ?>
        <div class="p-4 text-secondary"><?= gettext('No members are assigned to this house assembly yet.') ?></div>
        <?php else: foreach ($recentProfiles as $profile): ?>
        <a href="<?= InputUtils::escapeAttribute($sRootPath . '/people/view/' . $profile['id']) ?>" class="list-group-item list-group-item-action d-flex align-items-center py-3">
            <?php if ($profile['photoUrl'] !== null): ?><span class="avatar avatar-md adm-profile-avatar me-3" style="background-image: url(<?= InputUtils::escapeAttribute($profile['photoUrl']) ?>)"></span><?php else: ?><span class="avatar avatar-md bg-blue-lt text-blue me-3"><?= InputUtils::escapeHTML($profile['initials']) ?></span><?php endif; ?>
            <span class="flex-fill"><span class="fw-medium d-block"><?= InputUtils::escapeHTML($profile['name']) ?></span><span class="text-secondary small"><?= $profile['updatedAt'] !== '' ? sprintf(gettext('Updated on %s'), InputUtils::escapeHTML($profile['updatedAt'])) : gettext('Member profile') ?></span></span><i class="ti ti-chevron-right text-secondary"></i>
        </a>
        <?php endforeach; endif; ?>
    </div>
</div>

<?php
require SystemURLs::getDocumentRoot() . '/Include/Footer.php';
