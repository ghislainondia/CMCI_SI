<?php

use ChurchCRM\dto\SystemURLs;
use ChurchCRM\Utils\InputUtils;

require SystemURLs::getDocumentRoot() . '/Include/Header.php';
?>

<?php if (!empty($sGlobalMessage)) : ?>
<div class="alert alert-<?= InputUtils::escapeAttribute($sGlobalMessageClass ?? 'info') ?> alert-dismissible">
    <?= $sGlobalMessage ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h3 class="card-title mb-0"><?= gettext('Import modules and lessons (CSV)') ?></h3>
        <a href="<?= $sRootPath ?>/admin/system/bertoua/import-template.csv" class="btn btn-sm btn-outline-secondary">
            <i class="fa-solid fa-download me-1"></i><?= gettext('Download template') ?>
        </a>
    </div>
    <div class="card-body">
        <p class="text-body-secondary small mb-3">
            <?= gettext('One row per lesson. Columns: module, module_order (optional), lesson, lesson_order (optional). Separator ; or , (Excel FR).') ?>
        </p>
        <form method="post" action="<?= $sRootPath ?>/admin/system/bertoua/import-csv" enctype="multipart/form-data" class="row g-3 align-items-end"
              onsubmit="return confirmReplaceIfChecked();">
            <div class="col-md-6">
                <label for="csvFile" class="form-label"><?= gettext('CSV file') ?></label>
                <input type="file" class="form-control" id="csvFile" name="csvFile" accept=".csv,text/csv" required>
            </div>
            <div class="col-md-4">
                <div class="form-check mt-4">
                    <input class="form-check-input" type="checkbox" name="replaceExisting" id="replaceExisting" value="1">
                    <label class="form-check-label" for="replaceExisting">
                        <?= gettext('Replace existing catalog (deletes all modules, lessons, and notes)') ?>
                    </label>
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fa-solid fa-file-import me-1"></i><?= gettext('Import') ?>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title mb-0"><?= gettext('Add Module') ?></h3>
    </div>
    <div class="card-body">
        <form method="post" action="<?= $sRootPath ?>/admin/system/bertoua/module" class="row g-2 align-items-end">
            <div class="col-md-8">
                <label for="newModuleTitle" class="form-label"><?= gettext('Module title') ?></label>
                <input type="text" class="form-control" id="newModuleTitle" name="title" required maxlength="255">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fa-solid fa-plus me-1"></i><?= gettext('Add') ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php if (count($modules) === 0) : ?>
<div class="alert alert-info"><?= gettext('No modules defined yet.') ?></div>
<?php endif; ?>

<?php foreach ($modules as $module) :
    $moduleId = (int) $module['id'];
    $lessons = $lessonsByModule[$moduleId] ?? [];
    ?>
<div class="card mb-3">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h3 class="card-title mb-0">
            <span class="text-body-secondary me-2">#<?= (int) $module['sortOrder'] ?></span>
            <?= InputUtils::escapeHTML($module['title']) ?>
        </h3>
        <div class="d-flex gap-1">
            <form method="post" action="<?= $sRootPath ?>/admin/system/bertoua/module/<?= $moduleId ?>/delete"
                  onsubmit="return confirm('<?= InputUtils::escapeAttribute(gettext('Delete this module and all its lessons?')) ?>');">
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </form>
        </div>
    </div>
    <div class="card-body">
        <form method="post" action="<?= $sRootPath ?>/admin/system/bertoua/module/<?= $moduleId ?>/update" class="row g-2 mb-3">
            <div class="col-md-6">
                <label class="form-label"><?= gettext('Title') ?></label>
                <input type="text" class="form-control" name="title" value="<?= InputUtils::escapeAttribute($module['title']) ?>" required>
            </div>
            <div class="col-md-2">
                <label class="form-label"><?= gettext('Order') ?></label>
                <input type="number" class="form-control" name="sortOrder" value="<?= (int) $module['sortOrder'] ?>" min="1">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary w-100"><?= gettext('Update Module') ?></button>
            </div>
        </form>

        <h4 class="mb-2"><?= gettext('Lessons') ?></h4>
        <?php if (count($lessons) === 0) : ?>
        <p class="text-body-secondary"><?= gettext('No lessons in this module.') ?></p>
        <?php else : ?>
        <div class="table-responsive mb-3">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th class="w-1"><?= gettext('Order') ?></th>
                        <th><?= gettext('Title') ?></th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($lessons as $lesson) : ?>
                    <tr>
                        <td colspan="3" class="p-0 border-0">
                            <form method="post" action="<?= $sRootPath ?>/admin/system/bertoua/lesson/<?= (int) $lesson['id'] ?>/update" class="d-flex flex-wrap gap-2 p-2 align-items-center">
                                <input type="number" name="sortOrder" class="form-control form-control-sm" style="width:4rem"
                                       value="<?= (int) $lesson['sortOrder'] ?>" min="1">
                                <input type="text" name="title" class="form-control form-control-sm flex-grow-1"
                                       value="<?= InputUtils::escapeAttribute($lesson['title']) ?>" required>
                                <button type="submit" class="btn btn-sm btn-outline-secondary"><?= gettext('Save') ?></button>
                            </form>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td class="text-end">
                            <form method="post" action="<?= $sRootPath ?>/admin/system/bertoua/lesson/<?= (int) $lesson['id'] ?>/delete"
                                  class="d-inline"
                                  onsubmit="return confirm('<?= InputUtils::escapeAttribute(gettext('Delete this lesson?')) ?>');">
                                <button type="submit" class="btn btn-sm btn-ghost-danger">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <form method="post" action="<?= $sRootPath ?>/admin/system/bertoua/module/<?= $moduleId ?>/lesson" class="row g-2">
            <div class="col-md-8">
                <label class="form-label"><?= gettext('New lesson') ?></label>
                <input type="text" class="form-control" name="title" required maxlength="255">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fa-solid fa-plus me-1"></i><?= gettext('Add Lesson') ?>
                </button>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>

<?php if (count($modules) > 1) : ?>
<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title mb-0"><?= gettext('Module display order') ?></h3>
    </div>
    <div class="card-body">
        <form method="post" action="<?= $sRootPath ?>/admin/system/bertoua/modules/reorder">
            <label class="form-label"><?= gettext('Module IDs in order (comma-separated)') ?></label>
            <input type="text" class="form-control mb-2" name="order"
                   value="<?= InputUtils::escapeAttribute(implode(',', array_column($modules, 'id'))) ?>">
            <button type="submit" class="btn btn-secondary"><?= gettext('Apply order') ?></button>
        </form>
    </div>
</div>
<?php endif; ?>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
function confirmReplaceIfChecked() {
    var replaceBox = document.getElementById('replaceExisting');
    if (replaceBox && replaceBox.checked) {
        return confirm(<?= json_encode(
            gettext('This will delete all existing modules, lessons, and notes. Continue?'),
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT
        ) ?>);
    }
    return true;
}
</script>

<?php
require SystemURLs::getDocumentRoot() . '/Include/Footer.php';
