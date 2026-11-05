<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gaya_kustom', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 60);
            $table->string('slug', 80)->unique();
            $table->string('berkas');
            $table->unsignedInteger('ukuran');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gaya_kustom');
    }
};
