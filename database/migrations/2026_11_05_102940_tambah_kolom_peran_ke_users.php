<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('peran', 20)->default('penulis')->after('email');
            $table->boolean('is_active')->default(true)->after('peran');
            $table->string('slug')->nullable()->unique()->after('name');
            $table->text('bio')->nullable()->after('slug');
            $table->string('sso_subject')->nullable()->unique()->after('bio');
            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['peran', 'is_active', 'slug', 'bio', 'sso_subject']);
        });
    }
};
