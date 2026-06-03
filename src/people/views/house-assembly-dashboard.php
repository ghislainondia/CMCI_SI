<?php

use ChurchCRM\dto\ChurchVocabulary;
use ChurchCRM\dto\SystemURLs;
use ChurchCRM\Utils\InputUtils;

require SystemURLs::getDocumentRoot() . '/Include/Header.php';
?>

<div class="row mb-3">
    <div class="col-12 col-lg-6">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-primary text-white avatar rounded-circle">
                            <i class="fa-solid fa-house-chimney-user"></i>
                        </span>
                    </div>
                    <div class="col">
                        <div class="fw-medium"><?= InputUtils::escapeHTML($assemblyName) ?></div>
                        <div class="text-body-secondary"><?= ChurchVocabulary::houseAssembly() ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-success text-white avatar rounded-circle">
                            <i class="fa-solid fa-users"></i>
                        </span>
                    </div>
                    <div class="col">
                        <div class="fw-medium"><?= (int) $memberCount ?></div>
                        <div class="text-body-secondary"><?= ChurchVocabulary::houseAssemblyMembers() ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title mb-0"><?= ChurchVocabulary::houseAssemblyMembers() ?></h3>
    </div>
    <div class="card-body p-0">
        <?php if ($memberCount === 0): ?>
        <div class="p-4 text-body-secondary">
            <?= gettext('No members are assigned to this house assembly yet.') ?>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover table-vcenter mb-0">
                <thead>
                <tr>
                    <th class="w-1"></th>
                    <th><?= gettext('Name') ?></th>
                    <th><?= gettext('Email') ?></th>
                    <th><?= gettext('Phone') ?></th>
                    <th class="w-1"></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($memberRows as $row): ?>
                <tr>
                    <td>
                        <span class="avatar avatar-sm" style="background-image: url(<?= InputUtils::escapeAttribute($row['photoUrl']) ?>)"></span>
                    </td>
                    <td><?= InputUtils::escapeHTML($row['name']) ?></td>
                    <td>
                        <?php if ($row['email'] !== ''): ?>
                        <a href="mailto:<?= InputUtils::escapeAttribute($row['email']) ?>"><?= InputUtils::escapeHTML($row['email']) ?></a>
                        <?php else: ?>
                        <span class="text-body-secondary"><?= gettext('Unassigned') ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($row['phone'] !== '') : ?>
                        <?= InputUtils::escapeHTML($row['phone']) ?>
                        <?php else : ?>
                        <span class="text-body-secondary"><?= gettext('Unassigned') ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= InputUtils::escapeAttribute($row['viewUrl']) ?>" class="btn btn-sm btn-outline-primary">
                            <?= gettext('View') ?>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
require SystemURLs::getDocumentRoot() . '/Include/Footer.php';
