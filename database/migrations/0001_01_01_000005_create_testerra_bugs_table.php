<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = config('testerra.table_prefix', 'testerra_');

        Schema::create($prefix.'bugs', function (Blueprint $table) use ($prefix) {
            $table->id();
            $table->foreignId('assignment_id')->constrained($prefix.'assignments')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('severity')->default('medium');
            $table->timestamps();

            $table->index('severity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('testerra.table_prefix', 'testerra_').'bugs');
    }
};
