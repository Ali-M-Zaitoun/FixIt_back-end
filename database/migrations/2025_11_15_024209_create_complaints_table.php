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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->foreignId('citizen_id')->constrained()->onDelete('cascade');
            $table->string('type');
            $table->text('description');
            $table->enum('status', ['new', 'in_progress', 'resolved', 'rejected'])->default('new');
            $table->foreignId('ministry_branch_id')->constrained();
            $table->foreignId('governorate_id')->constrained();
            $table->string('city_name')->nullable();
            $table->string('street_name')->nullable();
            $table->foreignId('locked_by')->nullable()->constrained('employees');
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
