<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hostel_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->constrained('campuses')->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->unsignedInteger('capacity')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->unique(['campus_id', 'code']);
        });

        Schema::create('hostel_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('block_id')->constrained('hostel_blocks')->cascadeOnDelete();
            $table->string('room_number');
            $table->unsignedTinyInteger('capacity')->default(2);
            $table->timestamps();
            $table->unique(['block_id', 'room_number']);
        });

        Schema::create('hostel_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainee_id')->constrained('trainees')->cascadeOnDelete();
            $table->foreignId('room_id')->constrained('hostel_rooms')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->string('status')->default('active'); // active, vacated
            $table->date('checked_in_at')->nullable();
            $table->date('checked_out_at')->nullable();
            $table->timestamps();
            $table->unique(['trainee_id', 'semester_id'], 'hostel_assignments_natural');
            $table->index(['room_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel_assignments');
        Schema::dropIfExists('hostel_rooms');
        Schema::dropIfExists('hostel_blocks');
    }
};
