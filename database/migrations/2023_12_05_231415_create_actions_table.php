<?php

use App\Models\Enums\ActionStatus;
use App\Models\User;
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
        Schema::create('actions', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->smallInteger('status')->default(ActionStatus::WAITING);
            $table->string('job_uuid')->index();
            $table->morphs('actionable');
            $table->foreignIdFor(User::class)->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->text('info')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actions');
    }
};
