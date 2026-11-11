<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Fase 4: Modul GOMSN Operasional, Analitik KPI, Ekspor & Chatbot (§5.8, §7.7, §8).
     */
    public function up(): void
    {
        // 1. Pengajuan Dana dan Barang (§8.1)
        Schema::create('pengajuan', function (Blueprint $table): void {
            $table->id();
            $table->string('nomor_pengajuan', 50)->unique();
            $table->string('jenis', 20); // 'DANA', 'BARANG'
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('judul', 200);
            $table->text('keperluan');
            $table->decimal('nominal_total', 15, 2)->default(0.00);
            $table->string('status', 30)->default('draft');
            // draft -> menunggu_atasan -> menunggu_finance -> dicairkan -> menunggu_bukti -> selesai (+ ditolak, dibatalkan)

            $table->boolean('requires_executive_approval')->default(false);
            $table->string('current_approver_stage', 30)->nullable();

            // Disbursement / Pencairan Finance
            $table->string('bukti_transfer_path', 255)->nullable();
            $table->dateTime('transfer_at')->nullable();
            $table->foreignId('transfer_by')->nullable()->constrained('users')->nullOnDelete();

            // Realization / Nota Realisasi Karyawan
            $table->decimal('nominal_realisasi', 15, 2)->nullable();
            $table->decimal('selisih_realisasi', 15, 2)->nullable();
            $table->string('bukti_realisasi_path', 255)->nullable();
            $table->dateTime('realisasi_at')->nullable();
            $table->text('realisasi_notes')->nullable();
            $table->dateTime('realisasi_deadline')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status'], 'idx_pengajuan_user_status');
            $table->index('status', 'idx_pengajuan_status');
        });

        // 2. Pengajuan Dana Detail (§8.1)
        Schema::create('pengajuan_dana_detail', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuan')->cascadeOnDelete();
            $table->string('deskripsi', 255);
            $table->integer('jumlah')->default(1);
            $table->decimal('harga_satuan', 15, 2)->default(0.00);
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->timestamps();
        });

        // 3. Pengajuan Approvals (§8.1, AC-10)
        Schema::create('pengajuan_approvals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuan')->cascadeOnDelete();
            $table->string('stage', 30); // atasan, finance, executive
            $table->foreignId('approver_id')->constrained('users')->cascadeOnDelete();
            $table->string('action', 20); // approved, rejected
            $table->text('notes')->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->index(['pengajuan_id', 'stage'], 'idx_p_appr_stage');
        });

        // 4. Lembur & Rekonsiliasi Absensi (§8.2)
        Schema::create('lembur', function (Blueprint $table): void {
            $table->id();
            $table->string('nomor_lembur', 50)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal');
            $table->dateTime('jam_mulai');
            $table->dateTime('jam_selesai');
            $table->integer('durasi_menit');
            $table->text('alasan');
            $table->string('status', 30)->default('pending'); // pending, approved_atasan, rejected, paid
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();

            // Reconciliation with Clock-out
            $table->boolean('is_reconciled')->default(false);
            $table->string('reconciliation_flag', 50)->default('NORMAL'); // NORMAL, DISCREPANCY_OVER_TOLERANCE
            $table->dateTime('actual_clockout_at')->nullable();
            $table->integer('actual_diff_minutes')->nullable();

            // Finance Payment
            $table->dateTime('paid_at')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('payment_reference', 100)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'tanggal'], 'idx_lembur_user_date');
        });

        // 5. Penugasan Kerja / Tasks (§8.3)
        Schema::create('tasks', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->foreignId('assigner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assignee_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 30)->default('Pending Approval'); // Pending Approval, In Progress, Completed, Rejected
            $table->string('priority', 20)->default('medium'); // low, medium, high, urgent
            $table->dateTime('due_date')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->foreignId('tiket_id')->nullable()->constrained('tiket')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['assignee_id', 'status'], 'idx_tasks_assignee_status');
        });

        Schema::create('task_subtasks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->string('title', 255);
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
        });

        Schema::create('task_comments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('comment');
            $table->string('attachment_path', 255)->nullable();
            $table->timestamps();
        });

        // 6. Chatbot Asisten Internal (§5.8)
        Schema::create('chatbot_intents', function (Blueprint $table): void {
            $table->id();
            $table->string('intent_key', 50)->unique();
            $table->json('keywords');
            $table->text('response_text');
            $table->string('category', 50)->default('umum');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('chatbot_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('user_message');
            $table->string('matched_intent_key', 50)->nullable();
            $table->decimal('confidence_score', 5, 2)->default(0.00);
            $table->text('bot_response')->nullable();
            $table->boolean('is_answered')->default(true);
            $table->dateTime('created_at')->useCurrent();

            $table->index('created_at', 'idx_chatbot_logs_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_logs');
        Schema::dropIfExists('chatbot_intents');
        Schema::dropIfExists('task_comments');
        Schema::dropIfExists('task_subtasks');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('lembur');
        Schema::dropIfExists('pengajuan_approvals');
        Schema::dropIfExists('pengajuan_dana_detail');
        Schema::dropIfExists('pengajuan');
    }
};

