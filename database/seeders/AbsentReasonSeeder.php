<?php

namespace Database\Seeders;

use App\Models\AbsentReason;
use Illuminate\Database\Seeder;

class AbsentReasonSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['code' => 'MC', 'label' => 'Medical Certificate', 'is_excused' => true, 'counts_as_present' => false, 'order' => 1],
            ['code' => 'CUTI', 'label' => 'Cuti (Personal Leave)', 'is_excused' => true, 'counts_as_present' => false, 'order' => 2],
            ['code' => 'FAMILY', 'label' => 'Family Emergency', 'is_excused' => true, 'counts_as_present' => false, 'order' => 3],
            ['code' => 'SCHOOL_EVT', 'label' => 'School Event', 'is_excused' => true, 'counts_as_present' => true, 'order' => 4],
            ['code' => 'UNEXCUSED', 'label' => 'Unexcused Absence', 'is_excused' => false, 'counts_as_present' => false, 'order' => 5],
            ['code' => 'LATE', 'label' => 'Late Arrival', 'is_excused' => false, 'counts_as_present' => true, 'order' => 6],
        ];
        foreach ($defaults as $d) {
            AbsentReason::updateOrCreate(['code' => $d['code']], $d);
        }
    }
}
