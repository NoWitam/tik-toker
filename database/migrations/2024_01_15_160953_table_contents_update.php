<?php

use App\Models\Enums\ContentStatus;
use App\Models\Knowledge;
use App\Models\Series;
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
        Schema::table('contents', function (Blueprint $table) {
            if (Schema::hasColumn('contents', 'status')) {
                $table->dropColumn('status');
            }
        });

        Schema::table('contents', function (Blueprint $table) {
            
            $table->smallInteger('status')->default(ContentStatus::IN_PROCESS);
            $table->timestamp('publication_time');
            $table->foreignIdFor(Series::class);
        });

        Schema::table('actions', function (Blueprint $table) {
            $table->timestamp('finished_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropIndex('series_index');
            $table->dropIndex('knowledge_index');
            $table->dropColumn(['status', 'publication_time', 'series_id', 'knowledge_id']);
        });
    }
};
