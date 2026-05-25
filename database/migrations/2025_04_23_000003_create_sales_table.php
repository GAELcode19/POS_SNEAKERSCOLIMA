<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('total', 10, 2);
            $table->string('payment_method')->default('efectivo');
            $table->string('status')->default('completada');
            $table->boolean('is_online')->default(false);
            $table->string('customer_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
