<?php

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

<div class="container-fluid">
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
                <h3 class="card-title"><?= gettext('Meeting Information') ?></h3>
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
                            <optgroup label="<?= gettext('Families') ?>">
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
                <h3 class="card-title mb-0"><?= gettext('Attendance') ?></h3>
            </div>
            <div class="card-body">
                <p class="text-body-secondary small mb-3">
                    <?= gettext('The attendance list is filled with members of the selected organizer (family or group).') ?>
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

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success">
                <i class="fa-solid fa-check me-1"></i><?= gettext('Save') ?>
            </button>
            <a href="<?= $meetingId > 0 ? $sRootPath . '/meetings/view/' . $meetingId : $sRootPath . '/meetings/dashboard' ?>"
               class="btn btn-secondary">
                <?= gettext('Cancel') ?>
            </a>
        </div>
    </form>
</div>

<script>
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
        churchOrganizer: <?= json_encode(gettext('Select a family or group to list members. The church organization has no fixed member list.'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
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
