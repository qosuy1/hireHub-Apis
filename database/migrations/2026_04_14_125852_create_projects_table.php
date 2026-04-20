<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['fixed', 'hourly']);
            $table->decimal('budget', 8, 2)->unsigned();
            $table->timestamp('delivery_date')->nullable();
            $table->enum('status', ['open', 'closed', 'in_progress']);
            $table->timestamps();

            // Indexes for fast search and filtering
            $table->index('status');
            $table->index('type');
            $table->index('user_id');
            $table->index(['status', 'type']); // Composite index for filtered searches
            $table->index('created_at');
            // $table->index('budget');
            // $table->index('delivery_date');
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
