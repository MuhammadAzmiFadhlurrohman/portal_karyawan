<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('olts', function (Blueprint $table) {
            $table->id();
            $table->string('nama_olt', 100);
            $table->string('kode', 50)->nullable()->index();
            $table->string('lokasi', 255)->nullable();
            $table->string('ip_address', 50)->nullable();
            $table->text('keterangan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('pons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('olt_id')->constrained('olts')->cascadeOnDelete();
            $table->string('nama_pon', 150);
            $table->unsignedInteger('port_max')->default(8);
            $table->unsignedInteger('port_terisi')->default(0);
            $table->unsignedInteger('total_port')->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['olt_id', 'nama_pon']);
        });

        Schema::create('odps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pon_id')->constrained('pons')->cascadeOnDelete();
            $table->string('nama_odp', 150);
            $table->unsignedInteger('port_max')->default(8);
            $table->unsignedInteger('port_terpakai')->default(0);
            $table->double('latitude')->nullable()->default(0);
            $table->double('longitude')->nullable()->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['pon_id', 'nama_odp']);
        });

        Schema::create('odp_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('odp_id')->nullable()->constrained('odps')->nullOnDelete();
            $table->string('nomor_internet', 100)->index();
            $table->string('nama_user', 255);
            $table->string('layanan', 100)->nullable();
            $table->string('alamat', 255)->nullable();
            $table->string('nomor', 100)->nullable();
            $table->string('olt_name', 100)->nullable();
            $table->string('note', 255)->nullable();
            $table->text('keterangan')->nullable();
            $table->string('status', 50)->default('aktif');
            $table->timestamps();

            $table->index(['odp_id', 'nama_user']);
        });

        Schema::create('odp_user_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('odp_id')->nullable()->constrained('odps')->nullOnDelete();
            $table->foreignId('pon_id')->nullable()->constrained('pons')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nomor_internet', 100)->index();
            $table->string('nama_pon', 150)->nullable();
            $table->string('nama_odp', 150)->nullable();
            $table->string('nama_user', 255);
            $table->string('aksi', 50)->default('tambah');
            $table->text('keterangan')->nullable();
            $table->dateTime('waktu_input')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odp_user_histories');
        Schema::dropIfExists('odp_customers');
        Schema::dropIfExists('odps');
        Schema::dropIfExists('pons');
        Schema::dropIfExists('olts');
    }
};
