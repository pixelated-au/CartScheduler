<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('appointment', ['elder', 'ministerial servant'])->nullable()->after('mobile_phone');
            $table->enum('serving_as', ['field missionary', 'special pioneer', 'bethel family member', 'regular pioneer', 'publisher'])->default('publisher')->after('appointment');
        });
    }
};
