<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('prism_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prism_response_id')->constrained()->onDelete('cascade');
            $table->text('text');
            $table->string('finish_reason')->nullable();
            $table->unsignedInteger('prompt_tokens')->nullable();
            $table->unsignedInteger('completion_tokens')->nullable();
            $table->unsignedInteger('cache_write_input_tokens')->nullable();
            $table->unsignedInteger('cache_read_input_tokens')->nullable();
            $table->string('response_id');
            $table->string('model');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prism_steps');
    }
};
