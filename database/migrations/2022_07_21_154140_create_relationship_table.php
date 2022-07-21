<?php
/**
 * Project: ${PROJECT_NAME}
 * Owner: Pixelated
 * Copyright: 2022
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('relationship', static function (Blueprint $table) {
            $table->unsignedBigInteger('user1_id');
            $table->unsignedBigInteger('user2_id');

            $table->foreign('user1_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('user2_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relationship');
    }
};
