<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('institution');
            $table->string('year');
            $table->enum('type', [
                'CSEE', 
                'ACSEE', 
                'Degree', 
                'Diploma', 
                'Certificate', 
                'Certification', 
                'Professional'
            ])->default('Certificate');
            $table->enum('level', [
                'secondary',
                'tertiary',
                'professional',
                'certificate'
            ])->default('certificate');
            $table->string('file_path');
            $table->enum('file_type', ['pdf', 'image', 'doc', 'excel'])->default('pdf');
            $table->string('thumbnail_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};