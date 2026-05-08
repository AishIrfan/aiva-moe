<?php

namespace Database\Seeders;

use App\Models\Camera;
use App\Models\Campus;
use App\Models\Cohort;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Guardian;
use App\Models\Pensyarah;
use App\Models\Period;
use App\Models\Program;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Term;
use App\Models\Trainee;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(AbsentReasonSeeder::class);

        // ============ SCHOOL MODE seed (existing) ============

        $school = School::firstOrCreate(
            ['code' => 'SMK01'],
            ['name' => 'SMK Demo', 'state' => 'Selangor', 'district' => 'Petaling']
        );

        User::firstOrCreate(
            ['email' => 'admin@aiva.test'],
            ['name' => 'MOE Admin', 'password' => Hash::make('password'), 'role' => User::ROLE_MOE_ADMIN, 'mode' => 'moe']
        );
        User::firstOrCreate(
            ['email' => 'school@aiva.test'],
            ['name' => 'School Admin', 'password' => Hash::make('password'), 'role' => User::ROLE_SCHOOL_ADMIN, 'mode' => 'school', 'school_id' => $school->id]
        );
        User::firstOrCreate(
            ['email' => 'teacher@aiva.test'],
            ['name' => 'Cikgu Siti', 'password' => Hash::make('password'), 'role' => User::ROLE_TEACHER, 'mode' => 'school', 'school_id' => $school->id]
        );

        // POC demo viewer — ministry-level oversight on the School side
        // (parallel to BPG on the IPG side), with IPG mode explicitly disabled.
        // Sees MOE overview + all schools + every School-mode module; cannot
        // see, switch to, or hit any IPG route.
        User::firstOrCreate(
            ['email' => 'moe-poc@aiva.test'],
            ['name' => 'MOE POC Demo', 'password' => Hash::make('password'), 'role' => User::ROLE_MOE_VIEWER, 'mode' => 'moe']
        );

        Term::firstOrCreate(['school_id' => $school->id, 'academic_year' => '2026', 'name' => 'Term 1'], ['start_date' => '2026-01-05', 'end_date' => '2026-05-30', 'is_current' => true]);
        foreach ([['P1','07:30','08:30'],['P2','08:30','09:30'],['P3','09:30','10:30'],['P4','10:30','11:30'],['P5','11:30','12:30']] as $i => $p) {
            Period::firstOrCreate(['school_id' => $school->id, 'label' => $p[0]], ['start_time' => $p[1], 'end_time' => $p[2], 'order' => $i + 1]);
        }
        foreach (['MT' => 'Mathematics', 'EN' => 'English', 'BM' => 'Bahasa Melayu', 'SC' => 'Science'] as $code => $name) {
            Subject::firstOrCreate(['school_id' => $school->id, 'code' => $code], ['name' => $name]);
        }

        $gradeRows = [];
        foreach ([1, 2, 3, 4, 5] as $lvl) {
            $gradeRows[$lvl] = Grade::firstOrCreate(['school_id' => $school->id, 'name' => "Form $lvl"], ['level' => $lvl]);
        }
        $teacher = Teacher::firstOrCreate(['school_id' => $school->id, 'name' => 'Cikgu Siti'], ['subject_specialization' => 'Mathematics']);
        foreach ($gradeRows as $lvl => $g) {
            foreach (['A', 'B'] as $section) {
                SchoolClass::firstOrCreate(
                    ['school_id' => $school->id, 'grade_id' => $g->id, 'name' => "{$lvl}{$section}"],
                    ['capacity' => 40, 'homeroom_teacher_id' => $teacher->id]
                );
            }
        }

        $zone = Zone::firstOrCreate(['school_id' => $school->id, 'name' => 'Main Hall'], ['type' => 'indoor']);
        Camera::firstOrCreate(['school_id' => $school->id, 'name' => 'Cam-Hall-01'], ['zone_id' => $zone->id, 'online' => true]);

        $classes = SchoolClass::where('school_id', $school->id)->orderBy('id')->get()->values();
        foreach (range(1, 20) as $i) {
            $s = Student::firstOrCreate(
                ['school_id' => $school->id, 'student_number' => sprintf('S%05d', $i)],
                ['name' => "Student {$i}", 'gender' => $i % 2 ? 'F' : 'M', 'status' => 'active']
            );
            Guardian::firstOrCreate(['student_id' => $s->id, 'name' => "Parent {$i}"], ['is_primary' => true, 'phone' => '01'.rand(10,99).rand(1000000,9999999)]);

            if ($classes->isNotEmpty()) {
                $class = $classes[($i - 1) % $classes->count()];
                Enrollment::firstOrCreate(
                    ['student_id' => $s->id, 'school_class_id' => $class->id],
                    ['start_date' => '2026-01-05', 'is_active' => true]
                );
            }
        }

        // ============ IPG MODE seed ============

        // Programs lookup. PISMP is canonical for v1; the rest are pre-seeded
        // (inactive) to make the extensibility hook visible.
        $programs = [
            ['code' => 'PISMP',  'name' => 'Program Ijazah Sarjana Muda Perguruan',          'is_active' => true],
            ['code' => 'PPISMP', 'name' => 'Persediaan Program Ijazah Sarjana Muda Perguruan','is_active' => false],
            ['code' => 'KPLI',   'name' => 'Kursus Perguruan Lepas Ijazah',                  'is_active' => false],
            ['code' => 'PDPLI',  'name' => 'Program Diploma Pascasiswazah Lepasan Ijazah',   'is_active' => false],
        ];
        foreach ($programs as $p) {
            Program::firstOrCreate(['code' => $p['code']], ['name' => $p['name'], 'is_active' => $p['is_active']]);
        }
        $pismp = Program::where('code', Program::PISMP)->first();

        // Campus
        $campus = Campus::firstOrCreate(
            ['code' => 'IPGKBA01'],
            [
                'name'     => 'IPG Kampus Bahasa Antarabangsa',
                'state'    => 'Kuala Lumpur',
                'district' => 'Lembah Pantai',
            ]
        );

        // Current semester
        $semester = Semester::firstOrCreate(
            ['campus_id' => $campus->id, 'code' => 'SEM2-2026'],
            [
                'name'       => 'Semester 2, 2025/2026',
                'start_date' => '2026-03-02',
                'end_date'   => '2026-07-25',
                'is_current' => true,
            ]
        );

        // Cohorts (Program · Major · Intake)
        $cohortDefs = [
            ['major' => 'Matematik',      'intake_label' => 'Ambilan Jun 2024', 'intake_date' => '2024-06-10'],
            ['major' => 'Bahasa Melayu',  'intake_label' => 'Ambilan Jun 2024', 'intake_date' => '2024-06-10'],
            ['major' => 'Sains',          'intake_label' => 'Ambilan Jun 2024', 'intake_date' => '2024-06-10'],
        ];
        $cohorts = collect();
        foreach ($cohortDefs as $cd) {
            $cohorts->push(Cohort::firstOrCreate(
                [
                    'campus_id'    => $campus->id,
                    'program_id'   => $pismp->id,
                    'major'        => $cd['major'],
                    'intake_label' => $cd['intake_label'],
                ],
                ['intake_date' => $cd['intake_date'], 'status' => 'active']
            ));
        }

        // Pensyarah (one is the Penyelaras Praktikum)
        $pensyarahDefs = [
            ['name' => 'Dr. Halimah Razak',         'spec' => 'Matematik',      'coord' => true],
            ['name' => 'Dr. Faiz Ramlan',           'spec' => 'Bahasa Melayu',  'coord' => false],
            ['name' => 'Encik Wong Kit Mun',        'spec' => 'Sains',          'coord' => false],
            ['name' => 'Puan Nurul Syamiela',       'spec' => 'Pedagogi',       'coord' => false],
            ['name' => 'Dr. Aminuddin Hassan',      'spec' => 'Penyelidikan',   'coord' => false],
        ];
        foreach ($pensyarahDefs as $idx => $pd) {
            Pensyarah::firstOrCreate(
                ['campus_id' => $campus->id, 'staff_number' => sprintf('IPG-PSY-%03d', $idx + 1)],
                [
                    'name'                     => $pd['name'],
                    'specialization'           => $pd['spec'],
                    'is_practicum_coordinator' => $pd['coord'],
                ]
            );
        }

        // IPG Admin (now scoped to campus)
        $ipgAdmin = User::firstOrCreate(
            ['email' => 'ipg@aiva.test'],
            ['name' => 'IPG Admin', 'password' => Hash::make('password'), 'role' => User::ROLE_IPG_ADMIN, 'mode' => 'ipg']
        );
        if ($ipgAdmin->campus_id !== $campus->id) {
            $ipgAdmin->update(['campus_id' => $campus->id]);
        }

        // BPG Admin (ministry-level for IPG; no campus_id — picks one to drill in)
        User::firstOrCreate(
            ['email' => 'bpg@aiva.test'],
            ['name' => 'BPG Admin', 'password' => Hash::make('password'), 'role' => User::ROLE_BPG_ADMIN, 'mode' => 'ipg']
        );

        // Trainees (Guru Pelatih) — distributed across cohorts
        foreach (range(1, 12) as $i) {
            $cohort = $cohorts[($i - 1) % $cohorts->count()];
            $traineeNumber = sprintf('IPG-T-%05d', $i);
            $names = [
                'Aiman Hakimi', 'Nurul Aisyah', 'Wong Mei Lin', 'Sarisha Devi',
                'Muhammad Daniel', 'Siti Khadijah', 'Tan Wei Jian', 'Aaliyah Iskandar',
                'Lim Jia Hui', 'Faridah Ismail', 'Arvinder Singh', 'Hafiz Akmal',
            ];
            Trainee::firstOrCreate(
                ['trainee_number' => $traineeNumber],
                [
                    'campus_id' => $campus->id,
                    'cohort_id' => $cohort->id,
                    'name'      => $names[$i - 1] ?? "Trainee {$i}",
                    'gender'    => $i % 2 ? 'F' : 'M',
                    'status'    => 'active',
                ]
            );
        }

        // ============ IPG ROLE TEST ACCOUNTS ============
        // One login per IPG actor type. Emails are deliberately self-describing
        // so you can tell at a glance what each test account represents.
        // System routes them by `users.role` — the email is just for humans.
        //
        // Wired BEFORE IpgDemoSeeder so every Pensyarah has `user_id`
        // populated when demo data is generated. Otherwise dependent
        // rows (leave responses, attendance audit, discipline filers)
        // get NULL `created_by_user_id` / `responded_by_user_id` on
        // a fresh seed, since those columns derive from $pensyarah->user_id.

        // Promote Dr. Faiz Ramlan to Ketua Jabatan Bahasa Melayu (existing pensyarah).
        $kjPensyarah = Pensyarah::where('campus_id', $campus->id)
            ->where('name', 'Dr. Faiz Ramlan')
            ->first();
        if ($kjPensyarah) {
            $kjPensyarah->update([
                'is_ketua_jabatan' => true,
                'major_scope'      => 'Bahasa Melayu',
            ]);
        }

        // Resolve the existing Pensyarah rows we'll wire test accounts to.
        $penyelaras  = Pensyarah::where('campus_id', $campus->id)->where('is_practicum_coordinator', true)->first();
        $pensyarah   = Pensyarah::where('campus_id', $campus->id)->where('name', 'Encik Wong Kit Mun')->first();
        $pensyarah4  = Pensyarah::where('campus_id', $campus->id)->where('name', 'Puan Nurul Syamiela')->first();
        $pensyarah5  = Pensyarah::where('campus_id', $campus->id)->where('name', 'Dr. Aminuddin Hassan')->first();
        $firstTrainee = Trainee::where('campus_id', $campus->id)->orderBy('id')->first();

        // 1. Ketua Jabatan (Bahasa Melayu)
        $kjUser = User::firstOrCreate(
            ['email' => 'kj.bm@aiva.test'],
            ['name' => 'Dr. Faiz Ramlan', 'password' => Hash::make('password'),
             'role' => User::ROLE_KETUA_JABATAN, 'mode' => 'ipg', 'campus_id' => $campus->id]
        );
        if ($kjPensyarah && $kjPensyarah->user_id !== $kjUser->id) {
            $kjPensyarah->update(['user_id' => $kjUser->id]);
        }

        // 2. Penyelaras Praktikum (Pensyarah with coordinator flag)
        $penyelarasUser = User::firstOrCreate(
            ['email' => 'penyelaras@aiva.test'],
            ['name' => $penyelaras?->name ?? 'Penyelaras Praktikum', 'password' => Hash::make('password'),
             'role' => User::ROLE_PENSYARAH, 'mode' => 'ipg', 'campus_id' => $campus->id]
        );
        if ($penyelaras && $penyelaras->user_id !== $penyelarasUser->id) {
            $penyelaras->update(['user_id' => $penyelarasUser->id]);
        }

        // 3. Pensyarah (regular)
        $pensyarahUser = User::firstOrCreate(
            ['email' => 'pensyarah@aiva.test'],
            ['name' => $pensyarah?->name ?? 'Pensyarah', 'password' => Hash::make('password'),
             'role' => User::ROLE_PENSYARAH, 'mode' => 'ipg', 'campus_id' => $campus->id]
        );
        if ($pensyarah && $pensyarah->user_id !== $pensyarahUser->id) {
            $pensyarah->update(['user_id' => $pensyarahUser->id]);
        }

        // 4. Trainee (Guru Pelatih)
        $traineeUser = User::firstOrCreate(
            ['email' => 'trainee@aiva.test'],
            ['name' => $firstTrainee?->name ?? 'Trainee', 'password' => Hash::make('password'),
             'role' => User::ROLE_TRAINEE, 'mode' => 'ipg', 'campus_id' => $campus->id]
        );
        if ($firstTrainee && $firstTrainee->user_id !== $traineeUser->id) {
            $firstTrainee->update(['user_id' => $traineeUser->id]);
        }

        // 5. Additional Pensyarah accounts so all 5 Pensyarahs have user_ids.
        // Real production deployments wouldn't have lecturer rows without
        // login accounts, and downstream FKs on audit columns rely on this.
        if ($pensyarah4) {
            $pensyarah4User = User::firstOrCreate(
                ['email' => 'pensyarah.nurul@aiva.test'],
                ['name' => 'Puan Nurul Syamiela', 'password' => Hash::make('password'),
                 'role' => User::ROLE_PENSYARAH, 'mode' => 'ipg', 'campus_id' => $campus->id]
            );
            if ($pensyarah4->user_id !== $pensyarah4User->id) {
                $pensyarah4->update(['user_id' => $pensyarah4User->id]);
            }
        }
        if ($pensyarah5) {
            $pensyarah5User = User::firstOrCreate(
                ['email' => 'pensyarah.aminuddin@aiva.test'],
                ['name' => 'Dr. Aminuddin Hassan', 'password' => Hash::make('password'),
                 'role' => User::ROLE_PENSYARAH, 'mode' => 'ipg', 'campus_id' => $campus->id]
            );
            if ($pensyarah5->user_id !== $pensyarah5User->id) {
                $pensyarah5->update(['user_id' => $pensyarah5User->id]);
            }
        }

        // Phases 4–7 + Wave 2: academics + practicum + hostel + mini-LMS demo data.
        // Runs AFTER test accounts so all Pensyarahs have user_id populated
        // before dependent rows are seeded.
        $this->call(IpgDemoSeeder::class);

        // School-mode Class Recording demo (CLASS_RECORDING_CHECKLIST §11).
        // Idempotent; enables the feature on the demo school and seeds 6
        // recordings covering preserved / near-expiry / archived scenarios.
        $this->call(ClassRecordingDemoSeeder::class);
    }
}
