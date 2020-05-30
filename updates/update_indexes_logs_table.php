<?php

namespace Jacob\Logbook\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use October\Rain\Support\Facades\Schema;

class UpdateIndexesLogsTable extends Migration
{
    public function up(): void
    {
        Schema::table('jacob_logbook_logs', function(Blueprint $table) {
            $table->dropIndex('jacob_logbook_logs_model_index');
            $table->dropIndex('jacob_logbook_logs_model_key_index');
            $table->index([
                'model',
                'model_key',
                'updated_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('jacob_logbook_logs', function(Blueprint $table) {
            $table->dropIndex('jacob_logbook_logs_model_model_key_updated_at_index');
            $table->index('model');
            $table->index('model_key');
        });
    }
}
