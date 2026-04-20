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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('freelancer_id')->constrained('users' , 'id')->onDelete('cascade');
            $table->text('cover_letter');
            $table->decimal('amount' , 8, 2)->unsigned()->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->unsignedSmallInteger('delevery_time');
            $table->timestamps();

            // Unique constraint
            $table->unique(['project_id', 'freelancer_id']);
            // Indexes for fast search and filtering
            $table->index('project_id');
            $table->index('freelancer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
