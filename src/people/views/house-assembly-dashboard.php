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
        <div class="card-actions">
            <?php if ($familyId !== null): ?>
            <a href="<?= SystemURLs::getRootPath() ?>/PersonEditor.php?FamilyID=<?= $familyId ?>" class="btn btn-primary">
                <i class="fa-solid fa-user-plus me-2"></i><?= gettext('Add Member') ?>
            </a>
            <?php endif; ?>
        </div>
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
                        <div class="btn-group">
                            <a href="<?= InputUtils::escapeAttribute($row['viewUrl']) ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#removeMemberModal"
                                data-person-id="<?= $row['id'] ?>" data-person-name="<?= InputUtils::escapeAttribute($row['name']) ?>">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ─── Modal: Remove Member ─────────────────────────────────────────────────── -->
<div class="modal fade" id="removeMemberModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="<?= SystemURLs::getRootPath() ?>/people/house-assembly/remove-member">
                <input type="hidden" name="personId" id="removePersonId" value="">
                <div class="modal-header">
                    <h5 class="modal-title"><?= gettext('Remove Member') ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><?= gettext('Are you sure you want to remove') ?> <strong id="removePersonName"></strong> <?= gettext('from this house assembly?') ?></p>
                    <p class="text-body-secondary"><?= gettext('This person will no longer be associated with this house assembly family.') ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= gettext('Cancel') ?></button>
                    <button type="submit" class="btn btn-danger"><?= gettext('Remove') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Handle remove member modal
document.getElementById('removeMemberModal').addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const personId = button.getAttribute('data-person-id');
    const personName = button.getAttribute('data-person-name');

    document.getElementById('removePersonId').value = personId;
    document.getElementById('removePersonName').textContent = personName;
});
</script>

<?php
require SystemURLs::getDocumentRoot() . '/Include/Footer.php';
