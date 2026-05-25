<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        DB::table('categories')->insert([
            ['name' => 'Lifestyle', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Running', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Basketball', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Skateboarding', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
