<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('study_groups', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('subject')->nullable();
    $table->text('description')->nullable();
    $table->string('owner_uid');   // Firebase UID
    $table->string('owner_name');  // Leader name
    $table->string('join_code', 10);
    $table->timestamps();
});


    }

    public function down(): void
    {
        Schema::dropIfExists('study_groups');
    }
};
