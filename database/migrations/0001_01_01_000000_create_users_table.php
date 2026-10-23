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
        // 1. Tabel Roles
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nama_role'); // Admin, Pegawai, SPV, Finance
            $table->timestamps();
        });

        // 2. Tabel Users
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('password');
            $table->foreignId('role_id')->constrained('roles');
            $table->timestamps();
        });

        // 3. Tabel Data Pegawai (Identitas)
        Schema::create('pegawais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nama_lengkap');
            $table->string('nik')->nullable();
            $table->string('ttl')->nullable();
            $table->timestamps();
        });

        // 4. Tabel AP Method (Metode Penerimaan Pembayaran User)
        Schema::create('ap_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nama_bank'); // Contoh: BCA, Mandiri, Gopay
            $table->string('no_rekening');
            $table->string('atas_nama');
            $table->timestamps();
        });

        // 5. Tabel Tiket Pengajuan Dana
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('tipe_pengajuan', ['Reimburse BBM', 'Barang Rusak']);
            $table->integer('nominal_req');
            $table->integer('nominal_app')->nullable(); // Nominal yang disetujui
            $table->text('keterangan_req');
            $table->json('img_req'); // Array path gambar bukti (bisa > 1 gambar)
            $table->json('json_req'); // Data dinamis (KM/BBM atau Nama/Harga Barang)
            $table->string('status')->default('Menunggu SPV');
            $table->foreignId('ap_method_id')->nullable()->constrained('ap_methods');
            $table->timestamps();
        });

        // 6. Tabel Histori Approval Tiket
        Schema::create('ticket_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->string('role');
            $table->string('status_app');
            $table->text('catatan_app')->nullable();
            $table->timestamps();
        });

        // 7. Tabel Pembayaran (Finance)
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->integer('nominal');
            $table->foreignId('ap_method_id')->nullable()->constrained('ap_methods');
            $table->string('bukti_pembayaran'); // Path gambar bukti transfer
            $table->timestamps();
        });

        // Tabel bawaan session Laravel
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
