<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->after('name');
            $table->text('bio')->nullable()->after('username');
            $table->string('avatar_path')->nullable()->after('bio');
            $table->foreignId('state_id')->nullable()->after('avatar_path')->constrained()->nullOnDelete();
            $table->foreignId('city_id')->nullable()->after('state_id')->constrained()->nullOnDelete();
            $table->integer('reputation')->default(0)->after('city_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['state_id']);
            $table->dropForeign(['city_id']);
            $table->dropColumn(['username', 'bio', 'avatar_path', 'state_id', 'city_id', 'reputation']);
        });
    }
};
