<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
//        Schema::table('locations', function (Blueprint $table) {
//            $table->unsignedTinyInteger('min_volunteers')->change();
//            $table->unsignedTinyInteger('max_volunteers')->change();
//        });
        // This is done explicitly because the built-in Laravel (and Doctrine/DBAL) doesn't support TINYINT
        DB::statement("ALTER TABLE `locations` CHANGE `min_volunteers` `min_volunteers` TINYINT UNSIGNED NOT NULL");
        DB::statement("ALTER TABLE `locations` CHANGE `max_volunteers` `max_volunteers` TINYINT UNSIGNED NOT NULL");
    }
};
