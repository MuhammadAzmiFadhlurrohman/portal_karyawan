<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('office_locations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('name', 120);
            $table->string('address')->nullable();
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->unsignedInteger('radius_m')->default(50);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 16)->unique();
            $table->string('name', 100);
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('color', 32)->default('#1E40AF');
            $table->unsignedSmallInteger('late_tolerance_minutes')->default(15);
            $table->unsignedSmallInteger('break_minutes')->default(60);
            $table->boolean('is_day_off')->default(false);
            $table->boolean('is_piket')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('national_holidays', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->string('name', 150);
            $table->boolean('is_collective_leave')->default(false);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('monthly_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('date')->index();
            $table->foreignId('shift_id')->constrained('shifts')->restrictOnDelete();
            $table->foreignId('office_location_id')->nullable()->constrained('office_locations')->nullOnDelete();
            $table->boolean('is_locked')->default(false);
            $table->string('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'date']);
        });

        Schema::create('face_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->longText('encrypted_embedding');
            $table->string('primary_photo_hash', 128)->nullable();
            $table->string('reference_photo_path')->nullable();
            $table->string('model_version', 64)->default('insightface-buffalo_l-onnx-1.0');
            $table->unsignedTinyInteger('poses_count')->default(3);
            $table->decimal('quality_score', 5, 4)->default(0.9200);
            $table->timestamp('consent_at');
            $table->string('consent_ip', 45)->nullable();
            $table->timestamp('enrolled_at');
            $table->timestamps();
        });

        Schema::create('face_attempt_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('token', 96)->unique();
            $table->string('challenge_type', 32); // smile, blink, turn_left, turn_right
            $table->decimal('detected_quality', 5, 4)->default(0.9000);
            $table->timestamp('expires_at')->index();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('date')->index();
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->foreignId('office_location_id')->nullable()->constrained('office_locations')->nullOnDelete();
            $table->string('attendance_type', 24)->default('office'); // office, field, remote
            $table->string('status', 32)->default('present')->index(); // present, late, pending_review, incomplete, absent, leave
            $table->string('source', 32)->default('face')->index(); // face, manual, offline_sync, correction
            $table->timestamp('clock_in_at')->nullable();
            $table->timestamp('clock_out_at')->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->decimal('lat_in', 10, 7)->nullable();
            $table->decimal('lng_in', 10, 7)->nullable();
            $table->decimal('gps_accuracy_in', 8, 2)->nullable();
            $table->decimal('distance_m_in', 10, 2)->nullable();
            $table->decimal('lat_out', 10, 7)->nullable();
            $table->decimal('lng_out', 10, 7)->nullable();
            $table->decimal('gps_accuracy_out', 8, 2)->nullable();
            $table->boolean('is_mock_location')->default(false);
            $table->decimal('face_score', 5, 4)->nullable();
            $table->decimal('liveness_score', 5, 4)->nullable();
            $table->boolean('is_flagged')->default(false)->index();
            $table->string('flag_reason')->nullable();
            $table->unsignedInteger('late_minutes')->default(0);
            $table->unsignedInteger('work_duration_minutes')->default(0);
            $table->unsignedInteger('proposed_overtime_minutes')->default(0);
            $table->text('notes')->nullable();
            $table->string('ticket_reference', 64)->nullable();
            $table->string('photo_in_primary_path')->nullable();
            $table->string('photo_in_challenge_path')->nullable();
            $table->string('photo_out_path')->nullable();
            $table->foreignId('device_id')->nullable()->constrained('user_devices')->nullOnDelete();
            $table->string('client_uuid', 64)->nullable()->unique();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('face_attempt_tokens');
        Schema::dropIfExists('face_templates');
        Schema::dropIfExists('monthly_schedules');
        Schema::dropIfExists('national_holidays');
        Schema::dropIfExists('shifts');
        Schema::dropIfExists('office_locations');
    }
};
