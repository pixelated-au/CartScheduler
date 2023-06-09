<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    const DAY_AVAILABILITIES = [
        'morning',
        'afternoon',
        'evening',
        'half-day',
        'full-day',
        'not-available',
    ];

    public function up(): void
    {
        Schema::create('user_availabilities', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->primary('user_id');

            $table->set('day_monday', self::DAY_AVAILABILITIES);
            $table->set('day_tuesday', self::DAY_AVAILABILITIES);
            $table->set('day_wednesday', self::DAY_AVAILABILITIES);
            $table->set('day_thursday', self::DAY_AVAILABILITIES);
            $table->set('day_friday', self::DAY_AVAILABILITIES);

            $table->tinyInteger('num_saturdays')->unsigned()->default(0);
            $table->tinyInteger('num_sundays')->unsigned()->default(0);

            $table->text('comments')->nullable();

            $table->timestamps();
        });
    }
};
