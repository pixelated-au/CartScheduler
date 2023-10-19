<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->set('serving_as', ['field missionary', 'special pioneer', 'bethel family member', 'regular pioneer', 'publisher', 'elder', 'ministerial servant'])->after('mobile_phone');
        });
    }
};
