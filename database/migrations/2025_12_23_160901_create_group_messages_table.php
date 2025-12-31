<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('group_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id');
            $table->string('firebase_uid');
            $table->text('message')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();

            $table->foreign('group_id')
                  ->references('id')
                  ->on('study_groups')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_messages');
    }
};
