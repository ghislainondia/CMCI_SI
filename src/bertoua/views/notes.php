<?php

use ChurchCRM\dto\SystemURLs;
use ChurchCRM\Utils\InputUtils;

require SystemURLs::getDocumentRoot() . '/Include/Header.php';
?>

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title mb-0"><?= gettext('Context') ?></h3>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label for="assemblySelect" class="form-label"><?= InputUtils::escapeHTML($houseAssemblyLabel) ?></label>
                <select id="assemblySelect" class="form-select"<?= count($assemblies) <= 1 ? ' disabled' : '' ?>>
                    <?php if (count($assemblies) !== 1) : ?>
                    <option value=""><?= gettext('Select...') ?></option>
                    <?php endif; ?>
                    <?php foreach ($assemblies as $assembly) : ?>
                    <option value="<?= (int) $assembly['id'] ?>"
                        <?= $selectedGroupId === (int) $assembly['id'] ? 'selected' : '' ?>>
                        <?= InputUtils::escapeHTML($assembly['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="moduleSelect" class="form-label"><?= gettext('Module') ?></label>
                <select id="moduleSelect" class="form-select">
                    <option value=""><?= gettext('Select...') ?></option>
                    <?php foreach ($modules as $module) : ?>
                    <option value="<?= (int) $module['id'] ?>"
                        <?= $selectedModuleId === (int) $module['id'] ? 'selected' : '' ?>>
                        <?= InputUtils::escapeHTML($module['title']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="lessonSelect" class="form-label"><?= gettext('Lesson') ?></label>
                <select id="lessonSelect" class="form-select" disabled>
                    <option value=""><?= gettext('Select a module first...') ?></option>
                </select>
            </div>
        </div>
    </div>
</div>

<div id="notesPanel" class="card d-none">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0"><?= gettext('Member Notes') ?></h3>
        <button type="button" class="btn btn-primary" id="saveNotesBtn">
            <i class="fa-solid fa-floppy-disk me-1"></i><?= gettext('Save') ?>
        </button>
    </div>
    <div class="card-body p-0">
        <div id="notesLoading" class="p-4 text-body-secondary d-none"><?= gettext('Loading...') ?></div>
        <div id="notesEmpty" class="p-4 text-body-secondary d-none"><?= gettext('No members in this house assembly.') ?></div>
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="notesTable">
                <thead>
                    <tr>
                        <th><?= gettext('Member') ?></th>
                        <th><?= gettext('Note') ?></th>
                    </tr>
                </thead>
                <tbody id="notesTableBody"></tbody>
            </table>
        </div>
    </div>
</div>

<div id="notesHint" class="alert alert-info">
    <?= gettext('Select a house assembly, module, and lesson to enter notes.') ?>
</div>

<div id="saveAlert" class="alert d-none" role="alert"></div>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
(function () {
    const rootPath = <?= json_encode($sRootPath, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const initialLessonId = <?= (int) $selectedLessonId ?>;
    const initialModuleId = <?= (int) $selectedModuleId ?>;

    const assemblySelect = document.getElementById('assemblySelect');
    const moduleSelect = document.getElementById('moduleSelect');
    const lessonSelect = document.getElementById('lessonSelect');
    const notesPanel = document.getElementById('notesPanel');
    const notesHint = document.getElementById('notesHint');
    const notesTableBody = document.getElementById('notesTableBody');
    const notesLoading = document.getElementById('notesLoading');
    const notesEmpty = document.getElementById('notesEmpty');
    const saveNotesBtn = document.getElementById('saveNotesBtn');
    const saveAlert = document.getElementById('saveAlert');

    const i18n = {
        selectModule: <?= json_encode(gettext('Select a module first...'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
        selectLesson: <?= json_encode(gettext('Select...'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
        loadFailed: <?= json_encode(gettext('Failed to load data.'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
        saved: <?= json_encode(gettext('Notes saved successfully.'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    };

    function getContext() {
        return {
            groupId: parseInt(assemblySelect.value, 10) || 0,
            moduleId: parseInt(moduleSelect.value, 10) || 0,
            lessonId: parseInt(lessonSelect.value, 10) || 0,
        };
    }

    function contextReady() {
        const ctx = getContext();
        return ctx.groupId > 0 && ctx.moduleId > 0 && ctx.lessonId > 0;
    }

    function showAlert(message, type) {
        saveAlert.textContent = message;
        saveAlert.className = 'alert alert-' + type;
        saveAlert.classList.remove('d-none');
    }

    async function fetchJson(url) {
        const res = await fetch(url, { credentials: 'same-origin', headers: { Accept: 'application/json' } });
        const data = await res.json();
        if (!res.ok) {
            throw new Error(data.message || i18n.loadFailed);
        }
        return data;
    }

    async function loadLessons(moduleId, selectedLessonId) {
        lessonSelect.innerHTML = '<option value="">' + i18n.selectLesson + '</option>';
        lessonSelect.disabled = true;
        if (!moduleId) {
            lessonSelect.innerHTML = '<option value="">' + i18n.selectModule + '</option>';
            return;
        }
        const data = await fetchJson(rootPath + '/bertoua/api/lessons?moduleId=' + moduleId);
        data.lessons.forEach(function (lesson) {
            const opt = document.createElement('option');
            opt.value = lesson.id;
            opt.textContent = lesson.title;
            if (selectedLessonId && lesson.id === selectedLessonId) {
                opt.selected = true;
            }
            lessonSelect.appendChild(opt);
        });
        lessonSelect.disabled = false;
    }

    async function loadNotes() {
        if (!contextReady()) {
            notesPanel.classList.add('d-none');
            notesHint.classList.remove('d-none');
            return;
        }

        notesHint.classList.add('d-none');
        notesPanel.classList.remove('d-none');
        notesLoading.classList.remove('d-none');
        notesEmpty.classList.add('d-none');
        notesTableBody.innerHTML = '';

        const ctx = getContext();
        try {
            const membersData = await fetchJson(rootPath + '/bertoua/api/members?groupId=' + ctx.groupId);
            const notesData = await fetchJson(
                rootPath + '/bertoua/api/notes?groupId=' + ctx.groupId + '&lessonId=' + ctx.lessonId
            );

            notesLoading.classList.add('d-none');
            const members = membersData.members || [];
            if (members.length === 0) {
                notesEmpty.classList.remove('d-none');
                return;
            }

            const notes = notesData.notes || {};
            members.forEach(function (member) {
                const tr = document.createElement('tr');
                const existing = notes[member.id] ? notes[member.id].note : '';
                tr.innerHTML =
                    '<td class="align-middle">' + escapeHtml(member.name) + '</td>' +
                    '<td><textarea class="form-control note-field" rows="2" data-person-id="' + member.id + '">' +
                    escapeHtml(existing) + '</textarea></td>';
                notesTableBody.appendChild(tr);
            });
        } catch (err) {
            notesLoading.classList.add('d-none');
            showAlert(err.message, 'danger');
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    assemblySelect.addEventListener('change', function () {
        lessonSelect.value = '';
        loadNotes();
    });

    moduleSelect.addEventListener('change', async function () {
        const moduleId = parseInt(moduleSelect.value, 10) || 0;
        lessonSelect.value = '';
        try {
            await loadLessons(moduleId, 0);
        } catch (err) {
            showAlert(err.message, 'danger');
        }
        loadNotes();
    });

    lessonSelect.addEventListener('change', loadNotes);

    saveNotesBtn.addEventListener('click', async function () {
        if (!contextReady()) {
            return;
        }
        const ctx = getContext();
        const notes = {};
        document.querySelectorAll('.note-field').forEach(function (el) {
            notes[el.getAttribute('data-person-id')] = el.value;
        });

        saveNotesBtn.disabled = true;
        try {
            const res = await fetch(rootPath + '/bertoua/api/notes', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                body: JSON.stringify({
                    groupId: ctx.groupId,
                    lessonId: ctx.lessonId,
                    notes: notes,
                }),
            });
            const data = await res.json();
            if (!res.ok || !data.success) {
                throw new Error(data.message || i18n.loadFailed);
            }
            showAlert(data.message || i18n.saved, 'success');
            await loadNotes();
        } catch (err) {
            showAlert(err.message, 'danger');
        } finally {
            saveNotesBtn.disabled = false;
        }
    });

    (async function init() {
        if (initialModuleId > 0) {
            try {
                await loadLessons(initialModuleId, initialLessonId);
            } catch (err) {
                showAlert(err.message, 'danger');
            }
        }
        if (contextReady() || (parseInt(assemblySelect.value, 10) && initialModuleId && initialLessonId)) {
            if (initialLessonId) {
                lessonSelect.value = String(initialLessonId);
            }
            loadNotes();
        }
    })();
})();
</script>

<?php
require SystemURLs::getDocumentRoot() . '/Include/Footer.php';
