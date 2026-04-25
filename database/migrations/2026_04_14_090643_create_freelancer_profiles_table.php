<?php

use App\Enums\AvailabilityStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('freelancer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('phone')->unique();
            $table->text('bio');
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->string('avatar')->nullable();
            $table->json('portfolio_links')->nullable();
            // $table->json('skills_summary')->nullable();
            $table->enum('availability_status', AvailabilityStatusEnum::getValues())->default(AvailabilityStatusEnum::AVAILABLE->value);
            $table->decimal('average_rating', 2, 1)->default(0);

            // Indexes
            $table->index('availability_status');
            $table->index('average_rating');
            $table->index('hourly_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('freelancers');
    }
};
