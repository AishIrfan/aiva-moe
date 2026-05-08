@php
    $user = auth()->user();
    $mode = session('mode', $user?->mode ?? 'school');
    $schoolId = session('school_id') ?? $user?->school_id;
    $schoolName = session('school_name');
    $campusId   = session('campus_id') ?? $user?->campus_id;
    $campusName = session('campus_name');

    /**
     * Icon set — Phosphor-inspired stroke icons (stroke-width: 1.5, 20×20).
     * Defined once, referenced by key in the nav config. Keeps the rendered
     * markup quiet without pulling in a new dependency.
     */
    $i = [
        'home'         => '<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V20a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V9.5"/>',
        'calendarClock'=> '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 9h12"/><path d="M16 3v4M8 3v4"/><circle cx="18" cy="16" r="3"/><path d="M18 14.5V16l1 .8"/>',
        'cap'          => '<path d="M2 9.5 12 5l10 4.5L12 14 2 9.5Z"/><path d="M6 11v4c0 1.5 3 3 6 3s6-1.5 6-3v-4"/><path d="M22 9.5V14"/>',
        'userPlus'     => '<path d="M14 19a6 6 0 0 0-12 0"/><circle cx="8" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/>',
        'calendarGrid' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/><path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01"/>',
        'users'        => '<circle cx="9" cy="8" r="4"/><path d="M2 21a7 7 0 0 1 14 0"/><path d="M16 4a4 4 0 0 1 0 8"/><path d="M22 21a6 6 0 0 0-4-5.7"/>',
        'compass'      => '<circle cx="12" cy="12" r="9"/><path d="m15.5 8.5-2.5 6.5-6.5 2.5 2.5-6.5 6.5-2.5Z"/>',
        'bed'          => '<path d="M3 7v13M21 13v7M3 13h18"/><path d="M3 13a4 4 0 0 1 4-4h6a4 4 0 0 1 4 4"/><circle cx="7" cy="11" r="1.5"/>',
        'coins'        => '<ellipse cx="9" cy="7" rx="6" ry="3"/><path d="M3 7v5c0 1.5 2.7 3 6 3s6-1.5 6-3V7"/><path d="M21 11.5c0 1.7-2.7 3-6 3M21 11.5V17c0 1.5-2.7 3-6 3s-6-1.5-6-3v-1"/><path d="M21 11.5c0-1.7-2.7-3-6-3s-6 1.3-6 3"/>',
        'face'         => '<circle cx="12" cy="12" r="9"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><circle cx="9" cy="10" r="0.5" fill="currentColor"/><circle cx="15" cy="10" r="0.5" fill="currentColor"/>',
        'message'      => '<path d="M4 5h16v11H8l-4 4V5Z"/>',
        'fileText'     => '<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9l-6-6Z"/><path d="M14 3v6h6M8 13h8M8 17h6"/>',
        'monitorPlay' => '<rect x="2" y="4" width="20" height="14" rx="2"/><path d="M8 21h8M12 18v3"/><path d="m10 9 5 3-5 3V9Z"/>',
        'bell'         => '<path d="M6 9a6 6 0 0 1 12 0c0 6 3 7 3 7H3s3-1 3-7Z"/><path d="M10 21a2 2 0 0 0 4 0"/>',
        'shield'       => '<path d="M12 3 4 6v6c0 4.5 3.4 8.4 8 9 4.6-.6 8-4.5 8-9V6l-8-3Z"/><path d="m9 12 2 2 4-4"/>',
        'camera'       => '<path d="m23 8-6 4 6 4V8Z"/><rect x="1" y="6" width="16" height="12" rx="2"/>',
        'network'      => '<circle cx="5" cy="6" r="2.5"/><circle cx="19" cy="6" r="2.5"/><circle cx="12" cy="18" r="2.5"/><path d="M7 8l4 8M17 8l-4 8"/>',
        'trending'     => '<path d="m3 17 6-6 4 4 8-8"/><path d="M14 7h7v7"/>',
        'ticket'       => '<path d="M3 10V8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4Z"/><path d="M9 6v12"/>',
        'mail'         => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
        'clipboard'    => '<rect x="6" y="4" width="12" height="17" rx="2"/><path d="M9 4V3a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v1"/><path d="M9 10h6M9 14h6M9 18h4"/>',
        'check'        => '<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>',
        'barChart'     => '<path d="M3 21h18"/><path d="M7 21V11M12 21V5M17 21v-7"/>',
        'gear'         => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3h0a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5h0a1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8v0a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1Z"/>',
        'landmark'     => '<path d="M3 21h18M5 21V11M9 21V11M15 21V11M19 21V11"/><path d="m12 3-9 5h18l-9-5Z"/>',
        'building'     => '<rect x="4" y="3" width="16" height="18" rx="1.5"/><path d="M9 7h.01M14 7h.01M9 11h.01M14 11h.01M9 15h.01M14 15h.01"/><path d="M10 21v-4h4v4"/>',
        'beaker'       => '<path d="M9 3h6"/><path d="M10 3v7l-5 9a1 1 0 0 0 .9 1.5h12.2A1 1 0 0 0 19 19l-5-9V3"/><path d="M7 14h10"/>',
        'eye'          => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
    ];

    $schoolGroups = [
        'School System' => [
            ['label' => 'Overview',                 'route' => 'school.overview',     'icon' => $i['home']],
            ['label' => 'Schedule',                 'route' => 'school.schedule',     'icon' => $i['calendarClock'], 'sub' => 'Jadual Kelas'],
        ],
        'Academics' => [
            ['label' => 'Grades & Classes',         'route' => 'school.grades-classes', 'icon' => $i['cap']],
            ['label' => 'Enrollment',               'route' => 'school.enrollment',   'icon' => $i['userPlus']],
            ['label' => 'Timetables',               'route' => 'school.timetables',   'icon' => $i['calendarGrid']],
        ],
        'Students' => [
            ['label' => 'Students',                 'route' => 'school.students',         'icon' => $i['users']],
            ['label' => 'Student 360',              'route' => 'school.student-360',      'icon' => $i['compass']],
            ['label' => 'Leaves',                   'route' => 'school.leaves',           'icon' => $i['bed']],
            ['label' => 'Assistance',               'route' => 'school.assistance',       'icon' => $i['coins']],
            ['label' => 'Face Enrollment',          'route' => 'school.face-enrollment',  'icon' => $i['face']],
        ],
        'Communication' => [
            ['label' => 'Chat',                     'route' => 'school.chat',         'icon' => $i['message']],
            ['label' => 'Documents',                'route' => 'school.documents',    'icon' => $i['fileText']],
        ],
        'Safety & Incidents' => [
            ['label' => 'Live Monitor',             'route' => 'school.live',         'icon' => $i['monitorPlay']],
            ['label' => 'Alerts',                   'route' => 'school.alerts',       'icon' => $i['bell']],
            ['label' => 'Safety',                   'route' => 'school.safety',       'icon' => $i['shield']],
            ['label' => 'Cameras',                  'route' => 'school.cameras',      'icon' => $i['camera']],
            ['label' => 'Relationships',            'route' => 'school.relationship', 'icon' => $i['network']],
            ['label' => 'Analytics',                'route' => 'school.analytics',    'icon' => $i['trending']],
            // Class Recording is opt-in per school (CLASS_RECORDING_CHECKLIST §1, §3).
            // The entry is filtered out below when class_recording_enabled = false
            // for the active school. IPG mode never sees this group at all.
            ['label' => 'Class Recording',          'route' => 'school.class-recordings.index', 'icon' => $i['monitorPlay'], 'requiresSetting' => 'class_recording_enabled'],
        ],
        'Management' => [
            ['label' => 'Events',                   'route' => 'school.events-management',          'icon' => $i['ticket']],
            ['label' => 'Surat Cuti / MC',          'route' => 'school.surat-cuti-mc',              'icon' => $i['mail']],
            ['label' => 'Laporan Disiplin',         'route' => 'school.laporan-masalah-disiplin',   'icon' => $i['clipboard']],
            ['label' => 'Attendance',               'route' => 'school.attendance',                 'icon' => $i['check']],
            ['label' => 'Reports',                  'route' => 'school.reports',                    'icon' => $i['barChart']],
            ['label' => 'Settings',                 'route' => 'school.settings',                   'icon' => $i['gear']],
        ],
    ];

    // Filter out per-school opt-in entries (e.g. Class Recording) for schools
    // where the corresponding setting is disabled. Falls open for items without
    // a `requiresSetting` key. Done after the static array so the schoolGroups
    // structure stays declarative above.
    foreach ($schoolGroups as $groupName => $items) {
        $schoolGroups[$groupName] = array_values(array_filter($items, function ($item) use ($schoolId) {
            if (empty($item['requiresSetting'])) return true;
            if (! $schoolId) return false;
            return (bool) \App\Models\Setting::schoolValue($schoolId, $item['requiresSetting'], false);
        }));
    }

    $moeGroups = [
        'Ministry' => [
            ['label' => 'Overview',                 'route' => 'moe.overview',  'icon' => $i['landmark']],
            ['label' => 'Schools',                  'route' => 'moe.schools',   'icon' => $i['home']],
            ['label' => 'Trends',                   'route' => 'moe.trends',    'icon' => $i['trending']],
        ],
    ];

    /**
     * BPG (Bahagian Pendidikan Guru) — ministry layer above IPG campuses.
     * Rendered at the top of IPG-mode sidebar for BPG/MOE admins only.
     * Mirrors the MOE → School pattern: pick a campus to drill in.
     */
    $bpgGroups = [
        'Ministry (BPG)' => [
            ['label' => 'Overview',  'route' => 'bpg.overview',  'icon' => $i['landmark']],
            ['label' => 'Campuses',  'route' => 'bpg.campuses',  'icon' => $i['home']],
            ['label' => 'Trends',    'route' => 'bpg.trends',    'icon' => $i['trending']],
        ],
    ];

    /**
     * IPG nav — surface aligned with IPG_MODE_CHECKLIST.md §7.
     * Below we define the FULL admin sidebar, then derive role-specific
     * variants by composing subsets. Each variant is what that actor sees.
     */
    $ipgGroupsAdmin = [
        'Campus System' => [
            ['label' => 'Overview',                 'route' => 'ipg.overview',                       'icon' => $i['home']],
            ['label' => 'Academic Calendar',        'route' => 'ipg.academic-calendar',              'icon' => $i['calendarClock']],
        ],
        'Academics' => [
            ['label' => 'Grades & Cohorts',         'route' => 'ipg.grades-cohorts',                 'icon' => $i['cap']],
            ['label' => 'Enrollment',               'route' => 'ipg.enrollment',                     'icon' => $i['userPlus']],
            ['label' => 'Timetables',               'route' => 'ipg.timetables',                     'icon' => $i['calendarGrid']],
            ['label' => 'Transcripts',              'route' => 'ipg.transcripts',                    'icon' => $i['fileText']],
            ['label' => 'Co-curriculum',            'route' => 'ipg.cocurriculum',                   'icon' => $i['ticket']],
            ['label' => 'Research',                 'route' => 'ipg.research',                       'icon' => $i['beaker']],
        ],
        'Practicum' => [
            ['label' => 'Placements',               'route' => 'ipg.practicum.placements',           'icon' => $i['network']],
            ['label' => 'Supervisors',              'route' => 'ipg.practicum.supervisors',          'icon' => $i['users']],
            ['label' => 'Observations',             'route' => 'ipg.practicum.observations',         'icon' => $i['eye']],
            ['label' => 'Evaluations',              'route' => 'ipg.practicum.evaluations',          'icon' => $i['check']],
            ['label' => 'Logbook',                  'route' => 'ipg.practicum.logbook',              'icon' => $i['fileText'],     'sub' => 'Refleksi'],
            ['label' => 'School Coordination',      'route' => 'ipg.practicum.coordination',         'icon' => $i['mail']],
        ],
        'Trainees' => [
            ['label' => 'Trainees',                 'route' => 'ipg.trainees',                       'icon' => $i['users']],
            ['label' => 'Trainee 360',              'route' => 'ipg.trainee-360',                    'icon' => $i['compass']],
            ['label' => 'Leaves',                   'route' => 'ipg.leaves',                         'icon' => $i['bed']],
            ['label' => 'Assistance',               'route' => 'ipg.assistance',                     'icon' => $i['coins']],
            ['label' => 'Hostel',                   'route' => 'ipg.hostel',                         'icon' => $i['building'],     'sub' => 'Asrama'],
        ],
        'Communication' => [
            ['label' => 'Chat',                     'route' => 'ipg.chat',                           'icon' => $i['message']],
            ['label' => 'Documents',                'route' => 'ipg.documents',                      'icon' => $i['fileText']],
        ],
        'Management' => [
            ['label' => 'Events',                   'route' => 'ipg.events-management',              'icon' => $i['ticket']],
            ['label' => 'Surat Cuti / MC',          'route' => 'ipg.surat-cuti-mc',                  'icon' => $i['mail']],
            ['label' => 'Laporan Disiplin',         'route' => 'ipg.laporan-masalah-disiplin',       'icon' => $i['clipboard']],
            ['label' => 'Attendance',               'route' => 'ipg.attendance',                     'icon' => $i['check']],
            ['label' => 'Reports',                  'route' => 'ipg.reports',                        'icon' => $i['barChart']],
            ['label' => 'Settings',                 'route' => 'ipg.settings',                       'icon' => $i['gear']],
        ],
    ];

    /** Ketua Jabatan: full sidebar minus Settings (data still scoped by major in queries). */
    $ipgGroupsKetuaJabatan = $ipgGroupsAdmin;
    $ipgGroupsKetuaJabatan['Management'] = array_values(array_filter(
        $ipgGroupsKetuaJabatan['Management'],
        fn ($item) => $item['route'] !== 'ipg.settings'
    ));

    /** Penyelaras Praktikum: campus-wide Practicum + Pensyarah view. */
    $ipgGroupsPenyelaras = [
        'Campus System' => $ipgGroupsAdmin['Campus System'],
        'Academics' => [
            ['label' => 'Timetables',               'route' => 'ipg.timetables',                     'icon' => $i['calendarGrid']],
            ['label' => 'Transcripts',              'route' => 'ipg.transcripts',                    'icon' => $i['fileText']],
        ],
        'Practicum (campus-wide)' => $ipgGroupsAdmin['Practicum'],
        'Trainees' => [
            ['label' => 'Trainees',                 'route' => 'ipg.trainees',                       'icon' => $i['users']],
            ['label' => 'Trainee 360',              'route' => 'ipg.trainee-360',                    'icon' => $i['compass']],
        ],
        'Communication' => $ipgGroupsAdmin['Communication'],
    ];

    /** Pensyarah (regular): limited surface, scoped to assigned trainees. */
    $ipgGroupsPensyarah = [
        'Campus System' => $ipgGroupsAdmin['Campus System'],
        'Academics' => [
            ['label' => 'Timetables',               'route' => 'ipg.timetables',                     'icon' => $i['calendarGrid']],
            ['label' => 'Transcripts',              'route' => 'ipg.transcripts',                    'icon' => $i['fileText']],
        ],
        'My Practicum' => [
            ['label' => 'Placements',               'route' => 'ipg.practicum.placements',           'icon' => $i['network']],
            ['label' => 'Observations',             'route' => 'ipg.practicum.observations',         'icon' => $i['eye']],
            ['label' => 'Evaluations',              'route' => 'ipg.practicum.evaluations',          'icon' => $i['check']],
            ['label' => 'Logbook',                  'route' => 'ipg.practicum.logbook',              'icon' => $i['fileText'],     'sub' => 'Review'],
        ],
        'Trainees' => [
            ['label' => 'Trainees',                 'route' => 'ipg.trainees',                       'icon' => $i['users']],
            ['label' => 'Trainee 360',              'route' => 'ipg.trainee-360',                    'icon' => $i['compass']],
        ],
        'Communication' => $ipgGroupsAdmin['Communication'],
    ];

    /** Guru Pelatih (Trainee): completely "my-self" view. */
    $ipgGroupsTrainee = [
        'My Profile' => [
            ['label' => 'My profile',               'route' => 'ipg.trainee-360',                    'icon' => $i['compass']],
            ['label' => 'Academic calendar',        'route' => 'ipg.academic-calendar',              'icon' => $i['calendarClock']],
        ],
        'My Academics' => [
            ['label' => 'My timetable',             'route' => 'ipg.timetables',                     'icon' => $i['calendarGrid']],
            ['label' => 'My transcript',            'route' => 'ipg.transcripts',                    'icon' => $i['fileText']],
            ['label' => 'My co-curriculum',         'route' => 'ipg.cocurriculum',                   'icon' => $i['ticket']],
            ['label' => 'My research',              'route' => 'ipg.research',                       'icon' => $i['beaker']],
        ],
        'My Practicum' => [
            ['label' => 'My placement',             'route' => 'ipg.practicum.placements',           'icon' => $i['network']],
            ['label' => 'My logbook',               'route' => 'ipg.practicum.logbook',              'icon' => $i['fileText'],     'sub' => 'Refleksi'],
        ],
        'My Life' => [
            ['label' => 'My leaves',                'route' => 'ipg.leaves',                         'icon' => $i['bed']],
            ['label' => 'My assistance',            'route' => 'ipg.assistance',                     'icon' => $i['coins']],
            ['label' => 'My hostel',                'route' => 'ipg.hostel',                         'icon' => $i['building'],     'sub' => 'Asrama'],
        ],
        'Communication' => $ipgGroupsAdmin['Communication'],
    ];

    /**
     * Compose the active sidebar groups based on mode + role.
     * Also compute a small role badge (rendered under the campus chip) so the
     * user can see at a glance which actor they're logged in as.
     */
    $groups = [];
    $roleBadge = null;

    if ($mode === 'ipg' && ($user?->livesInIpgMode() || $user?->isMoe())) {
        // BPG ministry group only for BPG/MOE admins
        if ($user->isBpg() || $user->isMoe()) {
            $groups += $bpgGroups;
        }

        // Pick the role-specific IPG variant
        $ipgVariant = match (true) {
            $user->isTrainee()                                                     => $ipgGroupsTrainee,
            $user->isPensyarah() && $user->pensyarah?->is_practicum_coordinator    => $ipgGroupsPenyelaras,
            $user->isPensyarah()                                                   => $ipgGroupsPensyarah,
            $user->isKetuaJabatan()                                                => $ipgGroupsKetuaJabatan,
            default                                                                => $ipgGroupsAdmin, // IPG admin, BPG, MOE
        };
        $groups += $ipgVariant;

        // Role badge
        $roleBadge = match (true) {
            $user->isTrainee()                                                     => ['label' => 'Trainee · Guru Pelatih', 'tone' => 'sky',     'detail' => $user->trainee?->cohort?->display_name],
            $user->isPensyarah() && $user->pensyarah?->is_practicum_coordinator    => ['label' => 'Penyelaras Praktikum',   'tone' => 'emerald', 'detail' => 'Campus-wide practicum'],
            $user->isPensyarah()                                                   => ['label' => 'Pensyarah',              'tone' => 'zinc',    'detail' => $user->pensyarah?->specialization],
            $user->isKetuaJabatan()                                                => ['label' => 'Ketua Jabatan',          'tone' => 'amber',   'detail' => 'Major: ' . ($user->pensyarah?->major_scope ?? 'unspecified')],
            $user->isIpg()                                                         => ['label' => 'IPG Admin',              'tone' => 'emerald', 'detail' => null],
            $user->isBpg()                                                         => ['label' => 'BPG · Ministry',         'tone' => 'amber',   'detail' => null],
            $user->isMoe()                                                         => ['label' => 'MOE · Superadmin',       'tone' => 'emerald', 'detail' => null],
            default                                                                => null,
        };
    } elseif ($mode === 'moe' && ($user?->isMoe() || $user?->isMoeViewer())) {
        $groups += $moeGroups;
        if ($schoolId) $groups += $schoolGroups;
    } elseif ($user?->isMoe() || $user?->isMoeViewer()) {
        $groups += $moeGroups;
        if ($schoolId) $groups += $schoolGroups;
    } else {
        $groups = $schoolGroups;
    }
@endphp

<aside class="w-64 shrink-0 bg-white border-r border-zinc-200 flex flex-col lg:sticky lg:top-0 lg:h-[100dvh]"
       :class="sidebarOpen ? 'block' : 'hidden lg:flex'">

    {{-- Brand row --}}
    <div class="px-4 h-14 flex items-center justify-between border-b border-zinc-200">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
            <span class="relative inline-flex items-center justify-center w-7 h-7 rounded-md bg-zinc-900 text-emerald-400 font-semibold text-[13px] tracking-tight">
                <span class="absolute inset-0 rounded-md ring-1 ring-inset ring-white/10"></span>
                A
            </span>
            <span class="font-semibold tracking-tight text-zinc-900">AIVA <span class="text-zinc-400 font-medium">MOE</span></span>
        </a>
        <span class="text-[10px] font-mono tracking-[0.18em] text-zinc-400 uppercase">{{ $mode }}</span>
    </div>

    {{-- Contextual chip / prompt — varies by mode --}}
    @if ($mode === 'ipg')
        {{-- IPG / BPG: ACTIVE CAMPUS chip when a campus is in session, else "Pick a campus" --}}
        @if ($campusName)
            <div class="mx-3 mt-3 px-3 py-2 rounded-lg border border-zinc-200 bg-zinc-50/80 text-xs">
                <div class="text-[10px] uppercase tracking-wider text-zinc-400 font-medium">Active campus</div>
                <div class="text-zinc-900 truncate font-medium mt-0.5">{{ $campusName }}</div>
            </div>
        @elseif (($user?->isBpg() || $user?->isMoe()) && ! $campusId)
            <a href="{{ route('bpg.campuses') }}" class="mx-3 mt-3 flex items-start gap-2.5 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2.5 text-xs text-amber-900 hover:border-amber-300 transition">
                <svg class="w-4 h-4 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">{!! $i['compass'] !!}</svg>
                <div class="leading-snug">
                    <div class="font-medium">No campus selected</div>
                    <div class="text-amber-800/70 mt-0.5">Pick one to unlock campus navigation →</div>
                </div>
            </a>
        @endif

        {{-- Role badge — small chip showing which actor is logged in --}}
        @if ($roleBadge)
            <div class="mx-3 mt-2 px-2.5 py-1.5 rounded-md border
                        @if($roleBadge['tone']==='emerald') border-emerald-200 bg-emerald-50/70 @endif
                        @if($roleBadge['tone']==='amber') border-amber-200 bg-amber-50/70 @endif
                        @if($roleBadge['tone']==='sky') border-sky-200 bg-sky-50/70 @endif
                        @if($roleBadge['tone']==='zinc') border-zinc-200 bg-zinc-50/70 @endif">
                <div class="text-[10px] uppercase tracking-wider font-semibold
                            @if($roleBadge['tone']==='emerald') text-emerald-700 @endif
                            @if($roleBadge['tone']==='amber') text-amber-700 @endif
                            @if($roleBadge['tone']==='sky') text-sky-700 @endif
                            @if($roleBadge['tone']==='zinc') text-zinc-700 @endif">
                    {{ $roleBadge['label'] }}
                </div>
                @if ($roleBadge['detail'])
                    <div class="text-[11px] text-zinc-700 mt-0.5 truncate font-medium">{{ $roleBadge['detail'] }}</div>
                @endif
            </div>
        @endif
    @elseif (($user?->isMoe() || $user?->isMoeViewer()) && ! $schoolId)
        <a href="{{ route('moe.schools') }}" class="mx-3 mt-3 flex items-start gap-2.5 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2.5 text-xs text-amber-900 hover:border-amber-300 transition">
            <svg class="w-4 h-4 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">{!! $i['compass'] !!}</svg>
            <div class="leading-snug">
                <div class="font-medium">No school selected</div>
                <div class="text-amber-800/70 mt-0.5">Pick one to unlock school navigation →</div>
            </div>
        </a>
    @elseif ($schoolName && ($user?->isMoe() || $user?->isMoeViewer()))
        <div class="mx-3 mt-3 px-3 py-2 rounded-lg border border-zinc-200 bg-zinc-50/80 text-xs">
            <div class="text-[10px] uppercase tracking-wider text-zinc-400 font-medium">Active school</div>
            <div class="text-zinc-900 truncate font-medium mt-0.5">{{ $schoolName }}</div>
        </div>
    @endif

    {{-- Nav. Persists scroll position across page navigations via localStorage —
         Laravel re-renders the sidebar fresh on every request, so without this
         the scroll resets to the top whenever you click a nav item. --}}
    <nav class="flex-1 overflow-y-auto px-2 py-4 space-y-5 text-[13px]"
         x-data="{}"
         x-init="$el.scrollTop = parseInt(localStorage.getItem('aiva-sidebar-scroll') || 0)"
         @scroll.debounce.80ms="localStorage.setItem('aiva-sidebar-scroll', $el.scrollTop)">
        @foreach ($groups as $groupName => $items)
            <div>
                <div class="px-3 pb-2 text-[10px] font-semibold uppercase tracking-[0.14em] text-zinc-400">
                    {{ $groupName }}
                </div>
                <ul class="space-y-px">
                    @foreach ($items as $item)
                        @php $active = request()->routeIs($item['route']); @endphp
                        <li>
                            <a href="{{ route($item['route']) }}"
                               class="group relative flex items-center gap-2.5 rounded-md pl-3 pr-2 py-1.5 transition
                                      {{ $active
                                            ? 'nav-pill-active text-emerald-700 font-medium'
                                            : 'text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100' }}">
                                <svg class="w-[17px] h-[17px] shrink-0 {{ $active ? 'text-emerald-600' : 'text-zinc-400 group-hover:text-zinc-600' }}"
                                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                     stroke-linecap="round" stroke-linejoin="round">{!! $item['icon'] !!}</svg>
                                <span class="truncate">{{ $item['label'] }}</span>
                                @if (!empty($item['sub']))
                                    <span class="ml-auto text-[10px] font-mono text-zinc-400 group-hover:text-zinc-500">{{ $item['sub'] }}</span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </nav>

    @auth
        @php
            // Build the mode switcher dynamically based on what the user can access.
            $modes = [];
            if ($user->canSwitchMode() || $user->isSchoolAdmin() || $user->role === \App\Models\User::ROLE_TEACHER || $user->role === \App\Models\User::ROLE_OPERATOR) {
                $modes[] = ['key' => 'school', 'label' => 'School', 'disabled' => ! $schoolId, 'tip' => $schoolId ? null : 'Pick a school first'];
            }
            if ($user->canSwitchMode()) {
                $modes[] = ['key' => 'moe', 'label' => 'MOE',       'disabled' => false, 'tip' => null];
            }
            if ($user->isIpg() || $user->isBpg() || $user->canSwitchMode()) {
                $modes[] = ['key' => 'ipg', 'label' => 'IPG',       'disabled' => false, 'tip' => null];
            }
        @endphp

        @if (count($modes) > 1)
            <div class="border-t border-zinc-200 p-3">
                <div class="text-[10px] uppercase tracking-[0.14em] text-zinc-400 font-medium px-1 mb-1.5">Mode</div>
                <form method="POST" action="{{ route('mode.switch') }}"
                      class="grid gap-1 p-1 rounded-lg bg-zinc-100"
                      style="grid-template-columns: repeat({{ count($modes) }}, minmax(0,1fr));">
                    @csrf
                    @foreach ($modes as $m)
                        <button name="mode" value="{{ $m['key'] }}" {{ $m['disabled'] ? 'disabled' : '' }}
                                class="rounded-md px-2 py-1.5 text-xs font-medium transition disabled:opacity-40 disabled:cursor-not-allowed
                                       {{ $mode === $m['key']
                                            ? 'bg-white text-zinc-900 shadow-card'
                                            : 'text-zinc-500 hover:text-zinc-900' }}"
                                @if ($m['tip']) title="{{ $m['tip'] }}" @endif>{{ $m['label'] }}</button>
                    @endforeach
                </form>
            </div>
        @endif
    @endauth
</aside>
