<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collect_tasks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('waste_generation_point_id')->constrained()->cascadeOnDelete();
            $table->dateTime('scheduled_to');
            $table->string('state');
            $table->boolean('is_urgent')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collect_tasks');
    }
};
