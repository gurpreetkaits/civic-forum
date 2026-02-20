<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_verified')->default(false)->after('is_admin');
            $table->string('onfido_applicant_id')->nullable()->after('is_verified');
            $table->timestamp('verified_at')->nullable()->after('onfido_applicant_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_verified', 'onfido_applicant_id', 'verified_at']);
        });
    }
};
