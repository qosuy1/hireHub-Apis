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
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->string('file_path'); // attachments/xyz.jpg
            $table->string('file_type'); // mime_type: image/jpeg
            $table->unsignedBigInteger('file_size'); // KB size

            $table->morphs('attachable'); // attachable_id و attachable_type

            $table->timestamps();

            // Indexes for fast search and filtering
            $table->index('file_type');
            // $table->index('file_size');
            // $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
