<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_cancellations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('size')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('reason')->nullable();
            $table->string('alert_level')->default('bajo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_cancellations');
    }
};
