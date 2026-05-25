<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('asesor_ventas')->after('name');
            $table->string('status')->default('desconectado')->after('role');
            $table->date('hired_at')->nullable()->after('status');
            $table->decimal('weekly_hours_target', 4, 1)->default(40)->after('hired_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'status', 'hired_at', 'weekly_hours_target']);
        });
    }
};
