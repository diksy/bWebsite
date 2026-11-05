<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_run', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tema_id')->nullable()->constrained('tema')->nullOnDelete();
            $table->foreignId('artikel_id')->nullable()->constrained('artikel')->nullOnDelete();
            $table->string('status', 12)->default('menunggu')->index(); // menunggu, berjalan, selesai, gagal
            $table->string('topik', 200)->nullable();
            $table->json('data')->nullable(); // keadaan antar tahap: sumber, ringkasan, outline
            $table->text('pesan')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_run_id')->constrained('ai_run')->cascadeOnDelete();
            $table->string('tahap', 20);
            $table->string('status', 8); // ok, error
            $table->unsignedInteger('token_masuk')->default(0);
            $table->unsignedInteger('token_keluar')->default(0);
            $table->unsignedInteger('durasi_ms')->default(0);
            $table->text('pesan')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_log');
        Schema::dropIfExists('ai_run');
    }
};
