<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artikel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('kategori_id')->nullable()->constrained('kategori')->nullOnDelete();
            $table->string('judul', 160);
            $table->string('slug', 190)->unique();
            $table->string('ringkasan', 320)->nullable();
            $table->string('kata_kunci', 120)->nullable();
            $table->longText('isi')->nullable();
            $table->longText('isi_html')->nullable();
            $table->string('gambar')->nullable();
            $table->string('gambar_alt', 160)->nullable();
            $table->string('status', 12)->default('draf')->index();
            $table->timestamp('terbit_pada')->nullable()->index();
            $table->json('faq')->nullable();
            $table->json('sumber')->nullable();
            $table->boolean('dibuat_ai')->default(false);
            $table->timestamps();
        });

        Schema::create('artikel_tag', function (Blueprint $table) {
            $table->foreignId('artikel_id')->constrained('artikel')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('tag')->cascadeOnDelete();
            $table->primary(['artikel_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artikel_tag');
        Schema::dropIfExists('artikel');
    }
};
