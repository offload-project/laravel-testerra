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

        Schema::create($prefix.'screenshots', function (Blueprint $table) use ($prefix) {
            $table->id();
            $table->foreignId('bug_id')->constrained($prefix.'bugs')->cascadeOnDelete();
            $table->string('path');
            $table->string('disk');
            $table->string('original_filename');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('testerra.table_prefix', 'testerra_').'screenshots');
    }
};
