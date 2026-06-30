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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->longText('full_description')->nullable();
            $table->string('category')->nullable();
            $table->string('icon', 10)->nullable();
            $table->string('thumbnail')->nullable();
            $table->json('gallery')->nullable();
            $table->json('tech_stack')->nullable();
            $table->unsignedTinyInteger('team_size')->nullable();
            $table->year('year')->nullable();
            $table->string('client')->nullable();
            $table->string('duration')->nullable();
            $table->string('live_url')->nullable();
            $table->string('github_url')->nullable();
            $table->boolean('featured')->default(false);
            $table->boolean('active')->default(true);
            $table->unsignedBigInteger('views')->default(0);
            $table->timestamps();

            $table->index(['active', 'featured', 'year']);
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
