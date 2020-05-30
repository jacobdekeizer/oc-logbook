<?php

namespace Jacob\Logbook\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use October\Rain\Support\Facades\Schema;

class CreateLogsTable extends Migration
{
    public function up(): void
    {
        Schema::create('jacob_logbook_logs', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('model');
            $table->string('model_key');
            $table->unsignedInteger('backend_user_id')->nullable();
            $table->text('changes');
            $table->timestamps();

            $table->index('model');
            $table->index('model_key');
            $table->index('updated_at');

            $table->foreign('backend_user_id')
                ->references('id')
                ->on('backend_users')
                ->onDelete('set NULL');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jacob_logbook_logs');
    }
}
