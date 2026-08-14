<?php

use ChurchCRM\dto\SystemURLs;

require SystemURLs::getDocumentRoot() . '/Include/Header.php';

?>

<style nonce="<?= SystemURLs::getCSPNonce() ?>">
    /* Presentation shell only: FullCalendar, its event editor and existing controls are unchanged. */
    .calendar-premium {
        --cal-primary: #166c5d;
        --cal-primary-dark: #0c4d42;
        --cal-primary-soft: #e8f4f0;
        --cal-ink: #17251f;
        --cal-muted: #66756e;
        --cal-line: #e5ece8;
        --cal-canvas: #f5f8f6;
        --cal-radius: 16px;
        --cal-shadow: 0 8px 24px rgba(17, 47, 38, .06);
        animation: cal-enter .35s ease-out both;
        background: var(--cal-canvas);
        border-radius: var(--cal-radius);
        color: var(--cal-ink);
        padding: 1.25rem;
    }

    @keyframes cal-enter { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    .calendar-premium a:focus-visible, .calendar-premium button:focus-visible { outline: 3px solid rgba(226,166,66,.55); outline-offset: 3px; }
    .calendar-hero { background: linear-gradient(118deg, var(--cal-primary-dark), var(--cal-primary) 64%, #258976); border-radius: 20px; box-shadow: 0 12px 30px rgba(11,77,66,.18); color: #fff; margin-bottom: 1rem; overflow: hidden; padding: 1.45rem 1.5rem; position: relative; }
    .calendar-hero::after { border: 1px solid rgba(255,255,255,.15); border-radius: 50%; content: ''; height: 280px; pointer-events: none; position: absolute; right: -75px; top: -145px; width: 280px; }
    .calendar-hero > * { position: relative; z-index: 1; }
    .calendar-kicker { color: rgba(255,255,255,.76); font-size: .72rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
    .calendar-hero-title { font-size: clamp(1.45rem, 2.7vw, 2.05rem); font-weight: 700; letter-spacing: -.04em; line-height: 1.12; margin: .35rem 0 .65rem; }
    .calendar-hero-copy { color: rgba(255,255,255,.84); font-size: .92rem; line-height: 1.5; margin: 0; }
    .calendar-hero .btn-light { border: 0; color: var(--cal-primary-dark); font-weight: 700; }
    .calendar-hero .btn-outline-light { font-weight: 700; }
    #calendarTimezoneIndicator { backdrop-filter: blur(8px); background: rgba(255,255,255,.13); border: 1px solid rgba(255,255,255,.18); border-radius: 12px; color: rgba(255,255,255,.88) !important; margin: 0 !important; padding: .7rem .8rem; }
    #calendarTimezoneIndicator .badge { background: rgba(255,255,255,.18) !important; color: #fff !important; font-weight: 600; }
    #calendarTimezoneWarning { background: #fff0ce !important; color: #754b00 !important; }
    .calendar-premium .alert { border-radius: 13px; }
    .calendar-surface { background: #fff; border: 1px solid var(--cal-line); border-radius: var(--cal-radius); box-shadow: var(--cal-shadow); overflow: hidden; }
    .calendar-surface .card-body { padding: 1rem; }
    .calendar-surface #calendar { min-height: 680px; }
    .calendar-premium .fc .fc-toolbar-title { color: var(--cal-ink); font-size: 1.16rem; font-weight: 700; letter-spacing: -.02em; }
    .calendar-premium .fc .fc-button-primary { background: #f1f7f4; border-color: #dce7e1; color: var(--cal-primary-dark); font-weight: 700; }
    .calendar-premium .fc .fc-button-primary:hover, .calendar-premium .fc .fc-button-primary:not(:disabled).fc-button-active { background: var(--cal-primary); border-color: var(--cal-primary); color: #fff; }
    .calendar-premium .fc-theme-standard td, .calendar-premium .fc-theme-standard th { border-color: var(--cal-line); }
    .calendar-premium .fc .fc-daygrid-day-number { color: #50645c; font-size: .82rem; font-weight: 600; padding: .45rem; }
    .calendar-premium .fc .fc-day-today { background: #f0f8f5 !important; }
    .calendar-premium .fc .fc-col-header-cell-cushion { color: var(--cal-muted); font-size: .72rem; font-weight: 700; padding: .7rem .25rem; text-decoration: none; text-transform: uppercase; }
    .calendar-premium-sidebar .offcanvas-header { background: #f7fbf9; }
    .calendar-premium-sidebar .list-group-item { border-color: var(--cal-line); }
    .calendar-premium-sidebar .btn { border-radius: 9px; font-weight: 700; }

    @media (max-width: 575.98px) {
        .calendar-premium { border-radius: 0; margin: 0 -1rem; padding: 1rem; }
        .calendar-hero { padding: 1.3rem; }
        #calendarTimezoneIndicator { margin-top: 1rem !important; }
        .calendar-surface .card-body { padding: .55rem; }
        .calendar-surface #calendar { min-height: 560px; }
        .calendar-premium .fc .fc-toolbar { align-items: flex-start; flex-direction: column; gap: .75rem; }
        .calendar-premium .fc .fc-toolbar-chunk { display: flex; flex-wrap: wrap; gap: .25rem; }
        .calendar-premium .fc .fc-button { font-size: .75rem; padding: .4rem .5rem; }
    }
</style>

<main class="calendar-premium">
    <section class="calendar-hero" aria-label="<?= gettext('Calendar') ?>">
        <div class="row align-items-center g-3">
            <div class="col-lg-7">
                <div class="calendar-kicker"><i class="ti ti-calendar-event me-1"></i><?= gettext('Calendar') ?></div>
                <h1 class="calendar-hero-title"><?= gettext('Manage events, birthdays, and anniversaries') ?></h1>
                <p class="calendar-hero-copy"><?= gettext('Keep your church schedule visible and organize upcoming moments from one place.') ?></p>
                <div class="mt-3 d-flex flex-wrap gap-2">
                    <?php if ($canAddEvents): ?>
                    <a href="<?= htmlspecialchars($sRootPath, ENT_QUOTES, 'UTF-8') ?>/event/editor" class="btn btn-light"><i class="ti ti-plus me-1"></i><?= gettext('Add Church Event') ?></a>
                    <?php endif; ?>
                    <button type="button" class="btn btn-outline-light" data-bs-toggle="offcanvas" data-bs-target="#calendarSidebar" aria-controls="calendarSidebar"><i class="ti ti-stack-2 me-1"></i><?= gettext('Calendars') ?></button>
                </div>
            </div>
            <div class="col-lg-5">
                <!-- Calendar time-zone indicator. The script below reveals a warning only when browser and church zones differ. -->
                <div class="d-flex flex-wrap align-items-center small" id="calendarTimezoneIndicator">
                    <span class="me-2"><i class="fa fa-clock me-1"></i><?= _('Calendar time zone:') ?></span>
                    <span class="badge" id="calendarTimezoneConfigured"><?= htmlspecialchars($calendarJSArgs['sTimeZone'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="badge ms-2 d-none" id="calendarTimezoneWarning" role="alert">
                        <i class="fa fa-triangle-exclamation me-1"></i>
                        <span><?= _('Browser time zone differs:') ?></span>
                        <span id="calendarTimezoneBrowser" class="fw-semibold"></span>
                        <?php if ($isAdmin): ?>
                            <a href="<?= htmlspecialchars($sRootPath, ENT_QUOTES, 'UTF-8') ?>/admin/system/debug#collapseTimezone" class="ms-1 text-reset text-decoration-underline"><?= _('Details') ?></a>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        </div>
    </section>

<div class="alert alert-danger d-none" id="calendarApiWarning">
    <div class="d-flex align-items-center">
        <i class="ti ti-alert-triangle me-2"></i>
        <div>
            <h4 class="alert-title mb-1"><?= _('External Calendar API Disabled') ?></h4>
            <p class="mb-0"><?= _('Some calendars have access tokens, but external calendar sharing is currently disabled. Enable it via Calendar Settings to allow external apps to subscribe to your calendars.') ?></p>
        </div>
    </div>
</div>

<!-- Full-width calendar -->
<div class="calendar-surface">
    <div class="card-body p-0">
        <div id="calendar"></div>
    </div>
</div>

<!-- Calendar Sidebar Offcanvas -->
<div class="offcanvas offcanvas-end calendar-premium-sidebar" tabindex="-1" id="calendarSidebar" aria-labelledby="calendarSidebarLabel" style="width: 320px;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="calendarSidebarLabel">
            <i class="ti ti-stack-2 me-2 text-body-secondary"></i><?= _('Calendars') ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="<?= _('Close') ?>"></button>
    </div>
    <div class="offcanvas-body p-0">
        <!-- User Calendars -->
        <div class="px-3 pt-3 pb-1">
            <div class="d-flex align-items-center justify-content-between">
                <span class="text-uppercase text-body-secondary small fw-bold" style="letter-spacing:.05em;">
                    <i class="ti ti-user me-1"></i><?= _('My Calendars') ?>
                </span>
            </div>
        </div>
        <div class="list-group list-group-flush" id="calendarUserList"></div>
        <div class="px-3 py-2 d-none" id="addCalendarBtn">
            <button class="btn btn-sm btn-ghost-primary w-100">
                <i class="ti ti-circle-plus me-1"></i><?= _('New Calendar') ?>
            </button>
        </div>

        <hr class="my-0">

        <!-- System Calendars -->
        <div class="px-3 pt-3 pb-1">
            <span class="text-uppercase text-body-secondary small fw-bold" style="letter-spacing:.05em;">
                <i class="ti ti-settings me-1"></i><?= _('System Calendars') ?>
            </span>
        </div>
        <div class="list-group list-group-flush" id="calendarSystemList"></div>
    </div>
</div>

<div id="calendar-event-app"></div>
</main>

<!-- System Settings Panel Component -->
<?php if ($isAdmin): ?>
<link rel="stylesheet" href="<?= SystemURLs::assetVersioned('/skin/v2/system-settings-panel.min.css') ?>">
<script src="<?= SystemURLs::assetVersioned('/skin/v2/system-settings-panel.min.js') ?>" nonce="<?= SystemURLs::getCSPNonce() ?>"></script>
<?php
$calendarSettingsPanelConfig = [
    'container'           => '#calendarSettings',
    'title'               => gettext('Calendar Settings'),
    'icon'                => 'fa-solid fa-sliders',
    'headerClass'         => 'bg-info-lt',
    'showAllSettingsLink' => false,
    'settings'            => [
        [
            'name'    => 'bEnabledEvents',
            'type'    => 'boolean',
            'label'   => gettext('Enable Events Menu'),
            'tooltip' => gettext('Show or hide the Events menu in the main navigation.'),
        ],
        [
            'name'    => 'bEnableExternalCalendarAPI',
            'type'    => 'boolean',
            'label'   => gettext('Enable External Calendar API'),
            'tooltip' => gettext('Allow unauthenticated access to calendar events via public HTML, ICS, and JSON URLs. Required for sharing calendars with external apps.'),
        ],
    ],
];
?>
<script nonce="<?= SystemURLs::getCSPNonce() ?>">
window.CRM = window.CRM || {};
window.CRM.calendarSettingsPanel = <?= json_encode($calendarSettingsPanelConfig, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<?php endif; ?>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
window.CRM = window.CRM || {};
window.CRM.calendarJSArgs = <?= json_encode($calendarJSArgs, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) ?>;

// Reveal a warning next to the calendar time-zone badge when the browser's
// resolved time zone doesn't match the server's configured sTimeZone. A
// mismatch is the common silent cause of "my event shows at the wrong time".
// Both sides are canonicalized via Intl so that alias pairs like
// "US/Eastern" vs "America/New_York" or "Etc/UTC" vs "UTC" are treated as
// equal and do not trigger a false warning.
(function () {
    var configured = window.CRM.calendarJSArgs && window.CRM.calendarJSArgs.sTimeZone;
    if (!configured) return;
    var browser, canonicalConfigured;
    try {
        browser = Intl.DateTimeFormat().resolvedOptions().timeZone;
        canonicalConfigured = Intl.DateTimeFormat(undefined, { timeZone: configured }).resolvedOptions().timeZone;
    } catch (e) {
        return;
    }
    if (!browser || browser === canonicalConfigured) return;
    document.addEventListener('DOMContentLoaded', function () {
        var browserEl = document.getElementById('calendarTimezoneBrowser');
        var warnEl = document.getElementById('calendarTimezoneWarning');
        if (browserEl) browserEl.textContent = browser;
        if (warnEl) warnEl.classList.remove('d-none');
    });
})();
</script>

<script src="<?= SystemURLs::assetVersioned('/skin/v2/calendar-event-editor.min.js') ?>"></script>
<script src="<?= SystemURLs::assetVersioned('/skin/v2/event-calendars.min.js') ?>"></script>
<?php
require SystemURLs::getDocumentRoot() . '/Include/Footer.php';
