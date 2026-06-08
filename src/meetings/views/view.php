<?php

use ChurchCRM\dto\SystemURLs;
use ChurchCRM\Utils\InputUtils;

require SystemURLs::getDocumentRoot() . '/Include/Header.php';

$meetingId = (int) $meeting['id'];
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title mb-0"><?= gettext('Meeting Details') ?></h3>
                    <?php if ($canEdit) : ?>
                    <div class="ms-auto d-flex gap-2">
                        <a href="<?= $sRootPath ?>/meetings/editor/<?= $meetingId ?>"
                           class="btn btn-sm btn-outline-primary">
                            <i class="fa-solid fa-pen me-1"></i><?= gettext('Edit') ?>
                        </a>
                        <form method="post" action="<?= $sRootPath ?>/meetings/delete/<?= $meetingId ?>"
                              class="d-inline"
                              onsubmit="return confirm('<?= InputUtils::escapeAttribute(gettext('Delete this meeting?')) ?>');">
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="fa-solid fa-trash me-1"></i><?= gettext('Delete') ?>
                            </button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3"><?= gettext('Name') ?></dt>
                        <dd class="col-sm-9"><?= InputUtils::escapeHTML($meeting['name']) ?></dd>

                        <dt class="col-sm-3"><?= gettext('Date & Time') ?></dt>
                        <dd class="col-sm-9"><?= InputUtils::escapeHTML($sPageSubtitle) ?></dd>

                        <dt class="col-sm-3"><?= gettext('Organizer') ?></dt>
                        <dd class="col-sm-9"><?= InputUtils::escapeHTML($meeting['organizerLabel']) ?></dd>

                        <?php if (!empty($meeting['remarks'])) : ?>
                        <dt class="col-sm-3"><?= gettext('Remarks') ?></dt>
                        <dd class="col-sm-9"><?= nl2br(InputUtils::escapeHTML($meeting['remarks'])) ?></dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title"><?= gettext('Attendance') ?></h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-success">
                                <i class="fa-solid fa-check me-1"></i>
                                <?= gettext('Present') ?> (<?= count($presentAttendees) ?>)
                            </h6>
                            <?php if (empty($presentAttendees)) : ?>
                            <p class="text-body-secondary"><?= gettext('None') ?></p>
                            <?php else : ?>
                            <ul class="list-unstyled ms-2">
                                <?php foreach ($presentAttendees as $row) : ?>
                                <li class="mb-1">
                                    <a href="<?= $sRootPath ?>/people/view/<?= (int) $row['personId'] ?>">
                                        <?= InputUtils::escapeHTML($row['fullName']) ?>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-secondary">
                                <i class="fa-solid fa-xmark me-1"></i>
                                <?= gettext('Absent') ?> (<?= count($absentAttendees) ?>)
                            </h6>
                            <?php if (empty($absentAttendees)) : ?>
                            <p class="text-body-secondary"><?= gettext('None') ?></p>
                            <?php else : ?>
                            <ul class="list-unstyled ms-2">
                                <?php foreach ($absentAttendees as $row) : ?>
                                <li class="mb-1">
                                    <a href="<?= $sRootPath ?>/people/view/<?= (int) $row['personId'] ?>">
                                        <?= InputUtils::escapeHTML($row['fullName']) ?>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require SystemURLs::getDocumentRoot() . '/Include/Footer.php'; ?>
