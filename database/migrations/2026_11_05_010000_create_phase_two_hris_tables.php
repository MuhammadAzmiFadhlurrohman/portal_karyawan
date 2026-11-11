<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // §5.3 Tukar Shift dan Backup (4-Way Cross-Date Swap)
        Schema::create('shift_swaps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('target_user_id')->constrained('users')->cascadeOnDelete();
            $table->date('requester_date')->index();
            $table->date('target_date')->index();
            $table->boolean('is_backup')->default(false);

            // Snapshot of the 4 schedule shift IDs at request creation time (§5.3)
            $table->foreignId('req_d1_shift_id')->constrained('shifts')->restrictOnDelete();
            $table->foreignId('tar_d1_shift_id')->constrained('shifts')->restrictOnDelete();
            $table->foreignId('req_d2_shift_id')->nullable()->constrained('shifts')->restrictOnDelete();
            $table->foreignId('tar_d2_shift_id')->nullable()->constrained('shifts')->restrictOnDelete();

            $table->string('status', 32)->default('pending_target')->index(); // pending_target, pending_supervisor, approved, rejected, expired, cancelled
            $table->text('reason');
            $table->timestamp('expires_at')->index();
            $table->timestamp('target_approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // §5.4 & §9.3 Saldo Cuti Tahunan (unik user_id, year)
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedSmallInteger('annual_quota')->default(12);
            $table->unsignedSmallInteger('carried_over')->default(0);
            $table->unsignedSmallInteger('used_days')->default(0);
            $table->unsignedSmallInteger('alpa_deducted_days')->default(0);
            $table->integer('remaining_days')->default(12);
            $table->boolean('is_locked_override')->default(false);
            $table->foreignId('override_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('override_at')->nullable();
            $table->text('override_reason')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'year']);
        });

        // §5.4 & §9.1 Pengajuan Cuti & Konversi Alpa
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('leave_type', 32)->default('annual'); // annual, sick, special, alpa_conversion
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedSmallInteger('working_days')->default(1);
            $table->text('reason');
            $table->string('attachment_path')->nullable();
            $table->string('status', 32)->default('pending_supervisor')->index(); // pending_supervisor, pending_hrd, approved, rejected, cancelled, reversed
            $table->boolean('is_alpa_conversion')->default(false)->index();
            $table->timestamp('dispute_deadline_at')->nullable();
            $table->foreignId('supervisor_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('supervisor_approved_at')->nullable();
            $table->foreignId('hrd_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('hrd_approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejected_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'start_date']);
        });

        // §5.6 Koreksi Status Absensi
        Schema::create('attendance_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('attendance_id')->nullable()->constrained('attendances')->nullOnDelete();
            $table->date('date')->index();
            $table->string('requested_status', 32)->default('present'); // present, late
            $table->string('requested_attendance_type', 24)->default('office'); // office, field, remote
            $table->time('requested_clock_in_time')->nullable();
            $table->time('requested_clock_out_time')->nullable();
            $table->text('reason');
            $table->string('attachment_path')->nullable();
            $table->string('status', 32)->default('pending_supervisor')->index(); // pending_supervisor, pending_hrd, approved, rejected
            $table->boolean('is_post_closing_adjustment')->default(false);
            $table->foreignId('supervisor_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('supervisor_approved_at')->nullable();
            $table->foreignId('hrd_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('hrd_approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejected_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'date']);
        });

        // §5.5 & §9.2 Penguncian Periode Potongan Bulanan (AC-09)
        Schema::create('period_closings', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->boolean('is_locked')->default(true);
            $table->decimal('total_late_deduction', 15, 2)->default(0);
            $table->decimal('total_alpa_deduction', 15, 2)->default(0);
            $table->decimal('total_adjustment_amount', 15, 2)->default(0);
            $table->json('snapshot_data')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['year', 'month']);
        });

        // §5.5 & AC-09 Penyesuaian Potongan Lintas Periode (Adjustment setelah periode dikunci)
        Schema::create('deduction_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('source_year');
            $table->unsignedTinyInteger('source_month');
            $table->unsignedSmallInteger('applied_year');
            $table->unsignedTinyInteger('applied_month');
            $table->foreignId('attendance_correction_id')->nullable()->constrained('attendance_corrections')->nullOnDelete();
            $table->decimal('amount', 15, 2); // negatif = pengembalian/pengurang potongan, positif = tambahan potongan
            $table->string('description');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'applied_year', 'applied_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deduction_adjustments');
        Schema::dropIfExists('period_closings');
        Schema::dropIfExists('attendance_corrections');
        Schema::dropIfExists('leaves');
        Schema::dropIfExists('leave_balances');
        Schema::dropIfExists('shift_swaps');
    }
};
