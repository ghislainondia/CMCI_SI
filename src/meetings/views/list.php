<?php

use ChurchCRM\dto\SystemURLs;
use ChurchCRM\Utils\InputUtils;

require SystemURLs::getDocumentRoot() . '/Include/Header.php';
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title mb-0"><?= gettext('All Meetings') ?></h3>
            <?php if ($canEdit) : ?>
            <a href="<?= $sRootPath ?>/meetings/editor" class="btn btn-success btn-sm ms-auto">
                <i class="fa-solid fa-circle-plus me-1"></i><?= gettext('New Meeting') ?>
            </a>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <?php if (empty($meetings)) : ?>
            <p class="p-3 text-body-secondary mb-0"><?= gettext('No meetings found.') ?></p>
            <?php else : ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th><?= gettext('Name') ?></th>
                            <th><?= gettext('Date & Time') ?></th>
                            <th><?= gettext('Organizer') ?></th>
                            <th class="text-center"><?= gettext('Present') ?></th>
                            <th class="text-center"><?= gettext('Absent') ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($meetings as $meeting) : ?>
                        <tr>
                            <td><?= InputUtils::escapeHTML($meeting['name']) ?></td>
                            <td><?= InputUtils::escapeHTML($meeting['formattedDateTime']) ?></td>
                            <td><?= InputUtils::escapeHTML($meeting['organizerLabel']) ?></td>
                            <td class="text-center">
                                <span class="badge bg-success"><?= (int) $meeting['presentCount'] ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary"><?= (int) $meeting['absentCount'] ?></span>
                            </td>
                            <td class="text-end">
                                <a href="<?= $sRootPath ?>/meetings/view/<?= (int) $meeting['id'] ?>"
                                   class="btn btn-sm btn-outline-primary">
                                    <?= gettext('View') ?>
                                </a>
                                <?php if ($canEdit) : ?>
                                <a href="<?= $sRootPath ?>/meetings/editor/<?= (int) $meeting['id'] ?>"
                                   class="btn btn-sm btn-outline-secondary">
                                    <?= gettext('Edit') ?>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require SystemURLs::getDocumentRoot() . '/Include/Footer.php'; ?>
