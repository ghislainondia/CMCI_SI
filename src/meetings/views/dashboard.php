<?php

use ChurchCRM\dto\SystemURLs;
use ChurchCRM\Utils\DateTimeUtils;
use ChurchCRM\Utils\InputUtils;

require SystemURLs::getDocumentRoot() . '/Include/Header.php';
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-6 col-lg-3">
            <a href="<?= $sRootPath ?>/meetings/list" class="card card-sm text-decoration-none">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-primary text-white avatar rounded-circle">
                                <i class="fa-solid fa-handshake"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-medium text-body"><?= (int) $totalCount ?></div>
                            <div class="text-body-secondary"><?= gettext('Meetings') ?></div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <?php if ($canEdit) : ?>
        <div class="col-6 col-lg-3">
            <a href="<?= $sRootPath ?>/meetings/editor" class="card card-sm text-decoration-none">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-success text-white avatar rounded-circle">
                                <i class="fa-solid fa-circle-plus"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-medium text-body"><?= gettext('New Meeting') ?></div>
                            <div class="text-body-secondary"><?= gettext('Create') ?></div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <?php endif; ?>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title"><?= gettext('Upcoming Meetings') ?></h3>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($upcomingMeetings)) : ?>
                    <p class="p-3 text-body-secondary mb-0"><?= gettext('No upcoming meetings.') ?></p>
                    <?php else : ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($upcomingMeetings as $meeting) : ?>
                        <a href="<?= $sRootPath ?>/meetings/view/<?= (int) $meeting['id'] ?>"
                           class="list-group-item list-group-item-action">
                            <div class="fw-medium"><?= InputUtils::escapeHTML($meeting['name']) ?></div>
                            <small class="text-body-secondary d-block">
                                <?= DateTimeUtils::formatDate($meeting['meetingDateTime'], true) ?>
                            </small>
                            <small class="text-body-secondary"><?= InputUtils::escapeHTML($meeting['organizerLabel']) ?></small>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card mb-3">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title mb-0"><?= gettext('Recent Meetings') ?></h3>
                    <a href="<?= $sRootPath ?>/meetings/list" class="btn btn-sm btn-outline-primary ms-auto">
                        <?= gettext('View All') ?>
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($pastMeetings)) : ?>
                    <p class="p-3 text-body-secondary mb-0"><?= gettext('No past meetings yet.') ?></p>
                    <?php else : ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($pastMeetings as $meeting) : ?>
                        <a href="<?= $sRootPath ?>/meetings/view/<?= (int) $meeting['id'] ?>"
                           class="list-group-item list-group-item-action">
                            <div class="fw-medium"><?= InputUtils::escapeHTML($meeting['name']) ?></div>
                            <small class="text-body-secondary d-block">
                                <?= DateTimeUtils::formatDate($meeting['meetingDateTime'], true) ?>
                            </small>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require SystemURLs::getDocumentRoot() . '/Include/Footer.php'; ?>
