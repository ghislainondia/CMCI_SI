<?php

use ChurchCRM\dto\SystemURLs;
use ChurchCRM\Utils\InputUtils;

require SystemURLs::getDocumentRoot() . '/Include/Header.php';
?>

<style nonce="<?= SystemURLs::getCSPNonce() ?>">
    .bertoua-notes-premium { --bert-primary:#5c438f; --bert-primary-dark:#41306a; --bert-soft:#f0ecfb; --bert-ink:#251d36; --bert-muted:#706982; --bert-line:#e8e4f0; --bert-canvas:#f8f7fb; --bert-radius:16px; animation:bert-enter .35s ease-out both; background:var(--bert-canvas); border-radius:var(--bert-radius); color:var(--bert-ink); padding:1.25rem; }
    @keyframes bert-enter { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }
    .bertoua-hero { background:linear-gradient(118deg,var(--bert-primary-dark),var(--bert-primary) 64%,#7860b4); border-radius:20px; box-shadow:0 12px 30px rgba(56,42,91,.18); color:#fff; margin-bottom:1rem; overflow:hidden; padding:1.45rem 1.5rem; position:relative; }
    .bertoua-hero::after { border:1px solid rgba(255,255,255,.16); border-radius:50%; content:''; height:285px; pointer-events:none; position:absolute; right:-72px; top:-145px; width:285px; }
    .bertoua-hero > * { position:relative; z-index:1; }
    .bertoua-kicker { color:rgba(255,255,255,.76); font-size:.72rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; }
    .bertoua-title { font-size:clamp(1.45rem,2.7vw,2.05rem); font-weight:700; letter-spacing:-.04em; line-height:1.12; margin:.35rem 0 .65rem; }
    .bertoua-copy { color:rgba(255,255,255,.86); font-size:.92rem; line-height:1.5; margin:0; max-width:620px; }
    .bertoua-steps { display:flex; flex-wrap:wrap; gap:.5rem; justify-content:flex-lg-end; }
    .bertoua-step { backdrop-filter:blur(8px); background:rgba(255,255,255,.13); border:1px solid rgba(255,255,255,.18); border-radius:999px; font-size:.76rem; font-weight:600; padding:.48rem .7rem; }
    .bertoua-notes-premium .card { border:1px solid var(--bert-line); border-radius:var(--bert-radius); box-shadow:0 8px 24px rgba(46,35,75,.07); overflow:visible; }
    .bertoua-notes-premium .card-header { background:linear-gradient(90deg,#fbfaff,#fff); border-bottom-color:var(--bert-line); min-height:64px; padding:1rem 1.25rem; }
    .bertoua-notes-premium .card-title { color:var(--bert-ink); font-size:1rem; font-weight:700; letter-spacing:-.02em; }
    .bertoua-context-card .card-body { padding:1.25rem; }
    .bertoua-context-card label { color:#51486a; font-size:.8rem; font-weight:700; margin-bottom:.4rem; }
    .bertoua-context-card .form-select, .bertoua-notes-premium .note-field { border-color:#ddd6ed; border-radius:10px; }
    .bertoua-context-card .form-select { min-height:44px; }
    .bertoua-context-card .form-select:focus, .bertoua-notes-premium .note-field:focus { border-color:#a692d4; box-shadow:0 0 0 .2rem rgba(92,67,143,.13); }
    .bertoua-context-help { color:var(--bert-muted); font-size:.8rem; margin-top:.8rem; }
    #saveNotesBtn { background:var(--bert-primary); border-color:var(--bert-primary); border-radius:10px; font-weight:700; min-height:40px; }
    #saveNotesBtn:hover { background:var(--bert-primary-dark); border-color:var(--bert-primary-dark); }
    #notesTable thead th { background:#f8f6fc; color:var(--bert-muted); font-size:.69rem; font-weight:700; letter-spacing:.05em; text-transform:uppercase; }
    #notesTable td { border-color:var(--bert-line); vertical-align:middle; }
    #notesTable td:first-child { color:var(--bert-ink); font-weight:700; min-width:210px; }
    .bertoua-notes-premium .note-field { min-height:68px; }
    #notesHint { background:var(--bert-soft); border:1px solid #ddd5ee; border-radius:13px; color:#4f4074; }
    @media (max-width:575.98px) { .bertoua-notes-premium { border-radius:0; margin:0 -1rem; padding:1rem; } .bertoua-hero { padding:1.3rem; } .bertoua-steps { justify-content:flex-start; margin-top:1rem; } .bertoua-context-card .card-body, .bertoua-notes-premium .card-header { padding-left:1rem; padding-right:1rem; } #notesTable td:first-child { min-width:145px; } }
</style>

<main class="bertoua-notes-premium">
    <section class="bertoua-hero" aria-label="<?= gettext('Bertoua Message') ?>">
        <div class="row align-items-center g-3"><div class="col-lg-7"><div class="bertoua-kicker"><i class="ti ti-book-2 me-1"></i><?= gettext('Bertoua Message') ?></div><h1 class="bertoua-title"><?= gettext('A guided journey to learn to walk with God') ?></h1><p class="bertoua-copy"><?= gettext('Choose a lesson, review its questions with the members, and record their progress thoughtfully.') ?></p></div><div class="col-lg-5"><div class="bertoua-steps"><span class="bertoua-step"><i class="ti ti-building-community me-1"></i><?= InputUtils::escapeHTML($houseAssemblyLabel) ?></span><span class="bertoua-step"><i class="ti ti-book me-1"></i><?= gettext('Module') ?></span><span class="bertoua-step"><i class="ti ti-notebook me-1"></i><?= gettext('Lesson') ?></span></div></div></div>
    </section>

<div class="card bertoua-context-card mb-3">
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
                        <?= $selectedFamilyId === (int) $assembly['id'] ? 'selected' : '' ?>>
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
        <div class="bertoua-context-help"><i class="ti ti-info-circle me-1"></i><?= gettext('Select the assembly, module, and lesson before entering the members’ answers.') ?></div>
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

</main>

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
            familyId: parseInt(assemblySelect.value, 10) || 0,
            moduleId: parseInt(moduleSelect.value, 10) || 0,
            lessonId: parseInt(lessonSelect.value, 10) || 0,
        };
    }

    function contextReady() {
        const ctx = getContext();
        return ctx.familyId > 0 && ctx.moduleId > 0 && ctx.lessonId > 0;
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
            const membersData = await fetchJson(rootPath + '/bertoua/api/members?familyId=' + ctx.familyId);
            const notesData = await fetchJson(
                rootPath + '/bertoua/api/notes?familyId=' + ctx.familyId + '&lessonId=' + ctx.lessonId
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
                tr.className = 'bertoua-member-row';
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
                    familyId: ctx.familyId,
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
