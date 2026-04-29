<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Timetable Sessions — recurring weekly schedule for a course offering.
 *
 * One row = "this CourseOffering meets every Monday 09:00–10:30 in Room A203".
 * Source of truth for W1.3 (Pensyarah's "my timetable" view) and parent of
 * actual class-day attendance sessions in W1.1 (Wave 2).
 *
 * day_of_week uses ISO 8601: 1 = Monday ... 7 = Sunday.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_offering_id')->constrained('course_offerings')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 1=Mon..7=Sun
            $table->string('period_label')->nullable(); // optional e.g. "P1", "Slot 2"
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room')->nullable();
            $table->json('meta')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['course_offering_id', 'day_of_week', 'start_time'], 'timetable_sessions_by_offering_day');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_sessions');
    }
};
