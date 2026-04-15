<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collect_task_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('collect_task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('waste_id')->constrained()->cascadeOnDelete();
            $table->decimal('expected_quantity', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collect_task_items');
    }
};
