<?php

use ChurchCRM\dto\ChurchVocabulary;
use ChurchCRM\dto\SystemURLs;
use ChurchCRM\Utils\InputUtils;

require SystemURLs::getDocumentRoot() . '/Include/Header.php';

$formAction = $meetingId > 0
    ? $sRootPath . '/meetings/editor/' . $meetingId
    : $sRootPath . '/meetings/editor';

$name = $meeting['name'] ?? '';
$meetingDateTime = $meeting['meetingDateTime'] ?? date('Y-m-d H:i');
$organizerValue = $meeting['organizerValue'] ?? '';
$remarks = $meeting['remarks'] ?? '';

$attendanceByPerson = [];
foreach ($attendanceRows as $row) {
    $attendanceByPerson[(int) $row['personId']] = $row;
}
?>

<style nonce="<?= SystemURLs::getCSPNonce() ?>">
    .meeting-editor-premium { --me-primary:#166c5d; --me-primary-dark:#0c4d42; --me-ink:#17251f; --me-muted:#66756e; --me-line:#e5ece8; --me-canvas:#f5f8f6; --me-radius:16px; animation:me-enter .35s ease-out both; background:var(--me-canvas); border-radius:var(--me-radius); color:var(--me-ink); padding:1.25rem; }
    @keyframes me-enter { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }
    .meeting-editor-hero { background:linear-gradient(118deg,var(--me-primary-dark),var(--me-primary) 64%,#258976); border-radius:20px; box-shadow:0 12px 30px rgba(11,77,66,.18); color:#fff; margin-bottom:1rem; overflow:hidden; padding:1.45rem 1.5rem; position:relative; }
    .meeting-editor-hero::after { border:1px solid rgba(255,255,255,.15); border-radius:50%; content:''; height:280px; pointer-events:none; position:absolute; right:-75px; top:-145px; width:280px; }
    .meeting-editor-hero > * { position:relative; z-index:1; }
    .meeting-editor-kicker { color:rgba(255,255,255,.76); font-size:.72rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; }
    .meeting-editor-title { font-size:clamp(1.45rem,2.7vw,2.05rem); font-weight:700; letter-spacing:-.04em; line-height:1.12; margin:.35rem 0 .65rem; }
    .meeting-editor-copy { color:rgba(255,255,255,.86); font-size:.92rem; line-height:1.5; margin:0; }
    .meeting-editor-steps { display:flex; flex-wrap:wrap; gap:.5rem; justify-content:flex-lg-end; }
    .meeting-editor-step { backdrop-filter:blur(8px); background:rgba(255,255,255,.13); border:1px solid rgba(255,255,255,.18); border-radius:999px; font-size:.76rem; font-weight:600; padding:.48rem .7rem; }
    .meeting-editor-premium .card { border:1px solid var(--me-line); border-radius:var(--me-radius); box-shadow:0 8px 24px rgba(17,47,38,.06); margin-bottom:1rem; overflow:visible; }
    .meeting-editor-premium .card-header { background:linear-gradient(90deg,#f9fcfa,#fff); border-bottom-color:var(--me-line); min-height:64px; padding:1rem 1.25rem; }
    .meeting-editor-premium .card-title { color:var(--me-ink); font-size:1rem; font-weight:700; letter-spacing:-.02em; }
    .meeting-editor-premium .card-title .ti { color:var(--me-primary); font-size:1.1rem; }
    .meeting-editor-premium .card-body { padding:1.25rem; }
    .meeting-editor-premium label { color:#40554c; font-size:.81rem; font-weight:700; margin-bottom:.38rem; }
    .meeting-editor-premium .form-control, .meeting-editor-premium .form-select { border-color:#dce7e1; border-radius:10px; min-height:43px; }
    .meeting-editor-premium textarea.form-control { min-height:auto; }
    .meeting-editor-premium .form-control:focus, .meeting-editor-premium .form-select:focus { border-color:#7fcab5; box-shadow:0 0 0 .2rem rgba(22,108,93,.12); }
    #attendanceTable thead th { background:#f8fbf9; color:var(--me-muted); font-size:.68rem; font-weight:700; letter-spacing:.05em; text-transform:uppercase; }
    #attendanceTable td { border-color:var(--me-line); padding:.8rem .75rem; vertical-align:middle; }
    #attendanceTable tbody tr:hover { background:#f8fbf9; }
    #attendanceTable .form-check-input:checked { background-color:var(--me-primary); border-color:var(--me-primary); }
    .meeting-editor-actions { background:#fff; border:1px solid var(--me-line); border-radius:14px; box-shadow:0 8px 24px rgba(17,47,38,.06); margin-top:1.25rem; padding:.75rem; }
    .meeting-editor-actions .btn { border-radius:10px; font-weight:700; min-height:43px; }
    .meeting-editor-actions .btn-success { background:var(--me-primary); border-color:var(--me-primary); }
    .meeting-editor-actions .btn-success:hover { background:var(--me-primary-dark); border-color:var(--me-primary-dark); }
    @media (max-width:575.98px) { .meeting-editor-premium { border-radius:0; margin:0 -1rem; padding:1rem; } .meeting-editor-hero { padding:1.3rem; } .meeting-editor-steps { justify-content:flex-start; margin-top:1rem; } .meeting-editor-premium .card-header, .meeting-editor-premium .card-body { padding-left:1rem; padding-right:1rem; } .meeting-editor-actions { flex-wrap:wrap; } .meeting-editor-actions .btn { flex:1 1 100%; } }
</style>

<main class="meeting-editor-premium">
    <section class="meeting-editor-hero" aria-label="<?= $meetingId > 0 ? gettext('Edit Meeting') : gettext('New Meeting') ?>">
        <div class="row align-items-center g-3"><div class="col-lg-7"><div class="meeting-editor-kicker"><i class="ti ti-users-group me-1"></i><?= $meetingId > 0 ? gettext('Edit Meeting') : gettext('New Meeting') ?></div><h1 class="meeting-editor-title"><?= gettext('Schedule a meeting and record attendance') ?></h1><p class="meeting-editor-copy"><?= gettext('Create a clear meeting record, select the organizer, and follow the participation of each member.') ?></p></div><div class="col-lg-5"><div class="meeting-editor-steps"><span class="meeting-editor-step"><i class="ti ti-calendar-event me-1"></i><?= gettext('Meeting Information') ?></span><span class="meeting-editor-step"><i class="ti ti-users me-1"></i><?= gettext('Attendance') ?></span><span class="meeting-editor-step"><i class="ti ti-device-floppy me-1"></i><?= gettext('Save') ?></span></div></div></div>
    </section>
    <?php if (!empty($errors)) : ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error) : ?>
            <li><?= InputUtils::escapeHTML($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <form method="post" action="<?= InputUtils::escapeAttribute($formAction) ?>" id="meetingEditorForm">
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="ti ti-calendar-event me-2"></i><?= gettext('Meeting Information') ?></h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="mb-3 col-12 col-md-6">
                        <label for="Name" class="form-label"><?= gettext('Meeting Name') ?> *</label>
                        <input type="text" class="form-control" id="Name" name="Name" required
                               value="<?= InputUtils::escapeAttribute($name) ?>" maxlength="255">
                    </div>
                    <div class="mb-3 col-12 col-md-6">
                        <label for="MeetingDateTime" class="form-label"><?= gettext('Date & Time') ?> *</label>
                        <input type="datetime-local" class="form-control" id="MeetingDateTime" name="MeetingDateTime"
                               required value="<?= InputUtils::escapeAttribute(
                                   preg_match('/^\d{4}-\d{2}-\d{2}/', (string) $meetingDateTime)
                                       ? date('Y-m-d\TH:i', strtotime($meetingDateTime))
                                       : ''
                               ) ?>">
                    </div>
                    <div class="mb-3 col-12 col-md-6">
                        <label for="Organizer" class="form-label"><?= gettext('Organizer') ?> *</label>
                        <select class="form-select" id="Organizer" name="Organizer" required>
                            <option value=""><?= gettext('Select an organizer...') ?></option>
                            <?php if (!empty($organizerOptions['families'])) : ?>
                            <optgroup label="<?= ChurchVocabulary::houseAssemblies() ?>">
                                <?php foreach ($organizerOptions['families'] as $opt) : ?>
                                <option value="<?= InputUtils::escapeAttribute($opt['value']) ?>"
                                    <?= $organizerValue === $opt['value'] ? 'selected' : '' ?>>
                                    <?= InputUtils::escapeHTML($opt['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </optgroup>
                            <?php endif; ?>
                            <?php if (!empty($organizerOptions['groups'])) : ?>
                            <optgroup label="<?= gettext('Groups') ?>">
                                <?php foreach ($organizerOptions['groups'] as $opt) : ?>
                                <option value="<?= InputUtils::escapeAttribute($opt['value']) ?>"
                                    <?= $organizerValue === $opt['value'] ? 'selected' : '' ?>>
                                    <?= InputUtils::escapeHTML($opt['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </optgroup>
                            <?php endif; ?>
                            <?php if (!empty($organizerOptions['organizations'])) : ?>
                            <optgroup label="<?= gettext('Organizations') ?>">
                                <?php foreach ($organizerOptions['organizations'] as $opt) : ?>
                                <option value="<?= InputUtils::escapeAttribute($opt['value']) ?>"
                                    <?= $organizerValue === $opt['value'] ? 'selected' : '' ?>>
                                    <?= InputUtils::escapeHTML($opt['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </optgroup>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="mb-3 col-12">
                        <label for="Remarks" class="form-label"><?= gettext('Remarks') ?></label>
                        <textarea class="form-control" id="Remarks" name="Remarks" rows="4"><?= InputUtils::escapeHTML($remarks) ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title mb-0"><i class="ti ti-users me-2"></i><?= gettext('Attendance') ?></h3>
            </div>
            <div class="card-body">
                <p class="text-body-secondary small mb-3">
                    <?= sprintf(
                        gettext('The attendance list is filled with members of the selected organizer (%1$s or group).'),
                        ChurchVocabulary::houseAssembly()
                    ) ?>
                </p>
                <div class="row mb-3" id="addPersonRow">
                    <div class="col-md-8">
                        <label for="addPersonSelect" class="form-label"><?= gettext('Add a person') ?></label>
                        <select class="form-select" id="addPersonSelect" disabled>
                            <option value=""><?= gettext('Select an organizer first...') ?></option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="button" class="btn btn-secondary w-100" id="addPersonBtn">
                            <i class="fa-solid fa-user-plus me-1"></i><?= gettext('Add') ?>
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm" id="attendanceTable">
                        <thead>
                            <tr>
                                <th><?= gettext('Member') ?></th>
                                <th class="text-center" style="width: 140px;"><?= gettext('Present') ?></th>
                                <th class="text-center" style="width: 140px;"><?= gettext('Absent') ?></th>
                                <th style="width: 60px;"></th>
                            </tr>
                        </thead>
                        <tbody id="attendanceTableBody">
                            <?php foreach ($attendanceRows as $row) :
                                $pid = (int) $row['personId'];
                                $isPresent = !empty($row['isPresent']);
                                ?>
                            <tr data-person-id="<?= $pid ?>">
                                <td><?= InputUtils::escapeHTML($row['fullName']) ?></td>
                                <td class="text-center">
                                    <input type="radio" name="attendance[<?= $pid ?>]" value="present"
                                           <?= $isPresent ? 'checked' : '' ?> class="form-check-input">
                                </td>
                                <td class="text-center">
                                    <input type="radio" name="attendance[<?= $pid ?>]" value="absent"
                                           <?= !$isPresent ? 'checked' : '' ?> class="form-check-input">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-ghost-danger remove-attendee-btn" title="<?= gettext('Remove') ?>">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="text-body-secondary small mb-0" id="attendanceEmptyHint"
                   <?= !empty($attendanceRows) ? 'style="display:none;"' : '' ?>>
                    <?= gettext('Select an organizer to display its members.') ?>
                </p>
            </div>
        </div>

        <div class="meeting-editor-actions d-flex gap-2">
            <button type="submit" class="btn btn-success">
                <i class="fa-solid fa-check me-1"></i><?= gettext('Save') ?>
            </button>
            <a href="<?= $meetingId > 0 ? $sRootPath . '/meetings/view/' . $meetingId : $sRootPath . '/meetings/dashboard' ?>"
               class="btn btn-secondary">
                <?= gettext('Cancel') ?>
            </a>
        </div>
    </form>
</main>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
(function () {
    const root = window.CRM.root;
    const tbody = document.getElementById('attendanceTableBody');
    const emptyHint = document.getElementById('attendanceEmptyHint');
    const organizerSelect = document.getElementById('Organizer');
    const addPersonSelect = document.getElementById('addPersonSelect');
    const hasInitialAttendance = tbody.querySelectorAll('tr').length > 0;
    let organizerMembers = [];

    const i18n = {
        selectOrganizer: <?= json_encode(gettext('Select an organizer first...'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
        selectPerson: <?= json_encode(gettext('Select a person...'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
        noMembers: <?= json_encode(gettext('No members found for this organizer.'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
        churchOrganizer: <?= json_encode(
            sprintf(
                gettext('Select a %1$s or group to list members. The church organization has no fixed member list.'),
                ChurchVocabulary::houseAssembly()
            ),
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT
        ) ?>,
        emptyHint: <?= json_encode(gettext('Select an organizer to display its members.'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
        loadFailed: <?= json_encode(gettext('Failed to load members.'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    };

    function updateEmptyHint() {
        emptyHint.style.display = tbody.querySelectorAll('tr').length ? 'none' : 'block';
    }

    function escapeHtml(text) {
        const el = document.createElement('div');
        el.textContent = text;
        return el.innerHTML;
    }

    function getCurrentAttendanceState() {
        const state = {};
        tbody.querySelectorAll('tr[data-person-id]').forEach(function (tr) {
            const personId = tr.getAttribute('data-person-id');
            const presentRadio = tr.querySelector('input[value="present"]');
            state[personId] = presentRadio ? presentRadio.checked : true;
        });
        return state;
    }

    function rebuildAddPersonSelect(members) {
        addPersonSelect.innerHTML = '';
        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = members.length ? i18n.selectPerson : i18n.noMembers;
        addPersonSelect.appendChild(placeholder);

        members.forEach(function (m) {
            if (tbody.querySelector('tr[data-person-id="' + m.personId + '"]')) {
                return;
            }
            const opt = document.createElement('option');
            opt.value = String(m.personId);
            opt.textContent = m.fullName;
            addPersonSelect.appendChild(opt);
        });

        addPersonSelect.disabled = members.length === 0;
    }

    function clearAttendance() {
        tbody.innerHTML = '';
        organizerMembers = [];
        rebuildAddPersonSelect([]);
        updateEmptyHint();
    }

    function setAttendanceFromMembers(members, priorState) {
        tbody.innerHTML = '';
        organizerMembers = members;
        members.forEach(function (m) {
            const key = String(m.personId);
            const isPresent = Object.prototype.hasOwnProperty.call(priorState, key)
                ? priorState[key]
                : true;
            addRow(m.personId, m.fullName, isPresent);
        });
        rebuildAddPersonSelect(members);
    }

    function loadMembersFromOrganizer() {
        const organizer = organizerSelect.value;
        if (!organizer) {
            clearAttendance();
            addPersonSelect.innerHTML = '<option value="">' + escapeHtml(i18n.selectOrganizer) + '</option>';
            addPersonSelect.disabled = true;
            return;
        }

        if (organizer.indexOf('church:') === 0) {
            clearAttendance();
            emptyHint.textContent = i18n.churchOrganizer;
            emptyHint.style.display = 'block';
            return;
        }

        emptyHint.textContent = i18n.emptyHint;
        const priorState = getCurrentAttendanceState();

        $.getJSON(root + '/meetings/members', { organizer: organizer })
            .done(function (data) {
                const members = data.members || [];
                if (!members.length) {
                    clearAttendance();
                    window.CRM.notify(i18n.noMembers, { type: 'info' });
                    return;
                }
                setAttendanceFromMembers(members, priorState);
            })
            .fail(function () {
                window.CRM.notify(i18n.loadFailed, { type: 'danger' });
            });
    }

    function addRow(personId, fullName, isPresent) {
        personId = parseInt(personId, 10);
        if (!personId || tbody.querySelector('tr[data-person-id="' + personId + '"]')) {
            return;
        }
        const tr = document.createElement('tr');
        tr.setAttribute('data-person-id', String(personId));
        tr.innerHTML =
            '<td>' + escapeHtml(fullName) + '</td>' +
            '<td class="text-center"><input type="radio" name="attendance[' + personId + ']" value="present" class="form-check-input"' + (isPresent ? ' checked' : '') + '></td>' +
            '<td class="text-center"><input type="radio" name="attendance[' + personId + ']" value="absent" class="form-check-input"' + (!isPresent ? ' checked' : '') + '></td>' +
            '<td class="text-center"><button type="button" class="btn btn-sm btn-ghost-danger remove-attendee-btn" title="<?= InputUtils::escapeAttribute(gettext('Remove')) ?>"><i class="fa-solid fa-trash"></i></button></td>';
        tbody.appendChild(tr);
        updateEmptyHint();
    }

    tbody.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-attendee-btn');
        if (!btn) {
            return;
        }
        btn.closest('tr').remove();
        rebuildAddPersonSelect(organizerMembers);
        updateEmptyHint();
    });

    document.getElementById('addPersonBtn').addEventListener('click', function () {
        const opt = addPersonSelect.options[addPersonSelect.selectedIndex];
        if (!opt || !opt.value) {
            return;
        }
        addRow(opt.value, opt.text, true);
        rebuildAddPersonSelect(organizerMembers);
        addPersonSelect.value = '';
    });

    organizerSelect.addEventListener('change', loadMembersFromOrganizer);

    if (organizerSelect.value) {
        if (!hasInitialAttendance) {
            loadMembersFromOrganizer();
        } else {
            $.getJSON(root + '/meetings/members', { organizer: organizerSelect.value })
                .done(function (data) {
                    organizerMembers = data.members || [];
                    rebuildAddPersonSelect(organizerMembers);
                });
        }
    }

    updateEmptyHint();
})();
</script>

<?php require SystemURLs::getDocumentRoot() . '/Include/Footer.php'; ?>
