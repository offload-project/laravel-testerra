<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('testerra.table_prefix', 'testerra_').'tests', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('instructions');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('testerra.table_prefix', 'testerra_').'tests');
    }
};
