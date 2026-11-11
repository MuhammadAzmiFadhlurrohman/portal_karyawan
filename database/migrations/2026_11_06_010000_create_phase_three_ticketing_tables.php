<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Fase 3: Modul Work Report & Tiketing Gangguan (§7, §9).
     */
    public function up(): void
    {
        // 1. Ticket Sequences for atomic numbering (AC-04)
        Schema::create('ticket_sequences', function (Blueprint $table): void {
            $table->id();
            $table->string('prefix', 10); // 'BDG', 'BRD'
            $table->date('date');
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();

            $table->unique(['prefix', 'date'], 'uq_ticket_seq_prefix_date');
        });

        // 2. Master SLA per category & priority (§7.1)
        Schema::create('master_sla', function (Blueprint $table): void {
            $table->id();
            $table->string('kategori_tiket', 20); // 'BACKBONE', 'BROADBAND'
            $table->string('priority', 10);       // 'P1', 'P2', 'P3', 'P4'
            $table->integer('sla_minutes');
            $table->string('description', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['kategori_tiket', 'priority'], 'uq_sla_cat_priority');
        });

        // 3. Main Tiket table (§7.1, §7.2, §7.6, §9.3)
        Schema::create('tiket', function (Blueprint $table): void {
            $table->id();
            $table->string('no_tiket', 50)->unique();
            $table->string('kategori_tiket', 20); // 'BACKBONE', 'BROADBAND'
            $table->string('priority', 10)->default('P2'); // 'P1', 'P2', 'P3', 'P4'
            $table->string('status', 30)->default('OPEN'); // 'OPEN', 'PROSES', 'PENDING_VERIFIKASI', 'CLOSE', 'REOPENED', 'CANCELLED'
            $table->string('sla_status', 20)->nullable();  // 'TEPAT', 'LEBIH', 'NA'
            $table->integer('sla_target_minutes')->default(360);
            $table->integer('total_stop_clock_minutes')->default(0);
            $table->integer('net_mttr_minutes')->nullable();

            // Backbone specific fields
            $table->string('backbone_segment', 150)->nullable();
            $table->string('status_link_impact', 50)->nullable(); // TOTAL_DOWN, DEGRADED, NO_IMPACT
            $table->string('wilayah', 100)->nullable();
            $table->string('tipe_penanganan', 100)->nullable();

            // Broadband specific fields (IMS snapshot)
            $table->string('customer_id', 50)->nullable();
            $table->string('customer_name', 150)->nullable();
            $table->string('customer_phone', 30)->nullable();
            $table->text('customer_address')->nullable();
            $table->string('customer_odp', 100)->nullable();
            $table->string('customer_olt', 100)->nullable();
            $table->json('customer_snapshot')->nullable();
            $table->boolean('is_ims_validated')->default(true);

            // Technical closure & verification
            $table->text('problem_temuan')->nullable();
            $table->text('action_taken')->nullable();
            $table->decimal('redaman_sebelum', 6, 2)->nullable();
            $table->decimal('redaman_sesudah', 6, 2)->nullable();
            $table->boolean('has_link_up')->default(false);
            $table->foreignId('closing_awal_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('closing_awal_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->text('rejection_reason')->nullable();

            // Lifecyle timing
            $table->dateTime('tanggal_open');
            $table->dateTime('first_response_at')->nullable();
            $table->dateTime('pending_verification_at')->nullable();
            $table->dateTime('tanggal_close')->nullable();
            $table->unsignedInteger('reopened_count')->default(0);
            $table->dateTime('reopened_at')->nullable();
            $table->text('reopened_reason')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->text('cancelled_reason')->nullable();

            // Relationships
            $table->foreignId('parent_ticket_id')->nullable()->constrained('tiket')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'kategori_tiket', 'tanggal_open'], 'idx_tiket_status_cat_open');
            $table->index('customer_id', 'idx_tiket_cust_id');
            $table->index('backbone_segment', 'idx_tiket_bb_segment');
        });

        // 4. Ticket Assignees pivot (§7.2)
        Schema::create('ticket_assignees', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tiket_id')->constrained('tiket')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role_in_team', 20)->default('member'); // 'lead', 'member'
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('assigned_at');
            $table->timestamps();

            $table->unique(['tiket_id', 'user_id'], 'uq_ticket_user_assignee');
            $table->index('user_id', 'idx_assignees_user');
        });

        // 5. Ticket Status History (§7.2)
        Schema::create('ticket_status_history', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tiket_id')->constrained('tiket')->cascadeOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->index(['tiket_id', 'created_at'], 'idx_tsh_tiket_created');
        });

        // 6. Chronologies append-only (§7.3)
        Schema::create('ticket_chronologies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tiket_id')->constrained('tiket')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('category', 30); // IZIN, OTDR, TRACING, MATERIAL, JOINTING, LINK_UP, SELESAI, LAIN, SYSTEM
            $table->text('message');
            $table->string('photo_path', 255)->nullable();
            $table->string('video_path', 255)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('gps_accuracy', 6, 2)->nullable();
            $table->string('location_address', 255)->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->index(['tiket_id', 'created_at'], 'idx_chronology_tiket_created');
        });

        // 7. Interval Breaches (> 30 mins) (§7.3)
        Schema::create('interval_breaches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tiket_id')->constrained('tiket')->cascadeOnDelete();
            $table->dateTime('breached_at');
            $table->integer('elapsed_minutes');
            $table->foreignId('previous_chronology_id')->nullable()->constrained('ticket_chronologies')->nullOnDelete();
            $table->foreignId('current_chronology_id')->nullable()->constrained('ticket_chronologies')->nullOnDelete();
            $table->dateTime('created_at')->useCurrent();

            $table->index(['tiket_id'], 'idx_breach_tiket');
        });

        // 8. Stop Clocks (§7.4)
        Schema::create('stop_clocks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tiket_id')->constrained('tiket')->cascadeOnDelete();
            $table->string('reason', 50); // IZIN_AKSES, PIHAK_KETIGA_PLN, CUACA_EKSTRIM, MENUNGGU_MATERIAL, PERMINTAAN_PELANGGAN, FORCE_MAJEURE
            $table->text('reason_detail');
            $table->string('evidence_path', 255)->nullable();
            $table->dateTime('started_at');
            $table->dateTime('ended_at')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->foreignId('started_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('ended_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('approval_status', 20)->default('pending'); // 'pending', 'approved', 'rejected'
            $table->boolean('is_approved')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->text('approval_notes')->nullable();

            $table->timestamps();

            $table->index(['tiket_id', 'started_at'], 'idx_stopclock_tiket_started');
        });

        // 9. Ticket Handovers (§7.5)
        Schema::create('ticket_handovers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tiket_id')->constrained('tiket')->cascadeOnDelete();
            $table->string('shift_from', 50);
            $table->string('shift_to', 50);
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();
            $table->text('notes');
            $table->text('auto_summary')->nullable();
            $table->dateTime('acknowledged_at')->nullable();
            $table->dateTime('escalated_at')->nullable();
            $table->timestamps();

            $table->index(['tiket_id', 'created_at'], 'idx_handover_tiket_created');
        });

        // 10. Master Materials & Movements (§7.6)
        Schema::create('master_materials', function (Blueprint $table): void {
            $table->id();
            $table->string('kode', 50)->unique();
            $table->string('nama', 150);
            $table->string('satuan', 30)->default('pcs'); // meter, pcs, unit
            $table->integer('stok_tersedia')->default(0);
            $table->integer('min_stok')->default(5);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('material_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('master_material_id')->constrained('master_materials')->cascadeOnDelete();
            $table->foreignId('tiket_id')->nullable()->constrained('tiket')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 20); // OUT, IN_RETURN, ADJUSTMENT
            $table->integer('quantity');
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->text('notes')->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->index(['master_material_id', 'created_at'], 'idx_mat_movement');
        });

        Schema::create('ticket_materials', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tiket_id')->constrained('tiket')->cascadeOnDelete();
            $table->foreignId('master_material_id')->constrained('master_materials')->cascadeOnDelete();
            $table->integer('quantity');
            $table->foreignId('used_by')->constrained('users')->cascadeOnDelete();
            $table->dateTime('created_at')->useCurrent();
        });

        // 11. Repair Points, Joint Closure, Closure Cores, and Maneuver (§7.6)
        Schema::create('repair_points', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tiket_id')->constrained('tiket')->cascadeOnDelete();
            $table->string('nama_titik', 150);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('jenis_kerusakan', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('joint_closures', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tiket_id')->constrained('tiket')->cascadeOnDelete();
            $table->foreignId('repair_point_id')->nullable()->constrained('repair_points')->nullOnDelete();
            $table->string('nama_closure', 150);
            $table->integer('kapasitas_core')->default(24);
            $table->string('tipe_closure', 50)->nullable(); // Dome, In-line
            $table->timestamps();
        });

        Schema::create('closure_cores', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('joint_closure_id')->constrained('joint_closures')->cascadeOnDelete();
            $table->string('tube_asal', 20);
            $table->integer('core_asal');
            $table->string('tube_tujuan', 20);
            $table->integer('core_tujuan');
            $table->string('status_core', 30)->default('TERHUBUNG');
            $table->decimal('loss_db', 5, 2)->default(0.00);
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->unique(['joint_closure_id', 'tube_asal', 'core_asal'], 'uq_closure_tube_core_asal');
        });

        Schema::create('core_maneuvers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tiket_id')->constrained('tiket')->cascadeOnDelete();
            $table->string('segmen', 150);
            $table->integer('core_asal');
            $table->integer('core_tujuan');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // 12. Notification Logs, Preferences, & Message Templates (§7.8, §10.4)
        Schema::create('notification_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('channel', 20); // database, web_push, whatsapp
            $table->string('event_type', 50);
            $table->string('recipient', 100);
            $table->json('payload')->nullable();
            $table->string('status', 20)->default('queued'); // queued, sent, failed
            $table->integer('attempts')->default(0);
            $table->text('error_message')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->timestamps();

            $table->index(['channel', 'status', 'created_at'], 'idx_notif_log');
        });

        Schema::create('notification_preferences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('channel', 20);
            $table->string('event_type', 50);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'channel', 'event_type'], 'uq_user_notif_pref');
        });

        Schema::create('message_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('channel', 20);
            $table->string('subject_template', 255)->nullable();
            $table->text('body_template');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_templates');
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('core_maneuvers');
        Schema::dropIfExists('closure_cores');
        Schema::dropIfExists('joint_closures');
        Schema::dropIfExists('repair_points');
        Schema::dropIfExists('ticket_materials');
        Schema::dropIfExists('material_movements');
        Schema::dropIfExists('master_materials');
        Schema::dropIfExists('ticket_handovers');
        Schema::dropIfExists('stop_clocks');
        Schema::dropIfExists('interval_breaches');
        Schema::dropIfExists('ticket_chronologies');
        Schema::dropIfExists('ticket_status_history');
        Schema::dropIfExists('ticket_assignees');
        Schema::dropIfExists('tiket');
        Schema::dropIfExists('master_sla');
        Schema::dropIfExists('ticket_sequences');
    }
};
