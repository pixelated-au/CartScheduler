<?php

use App\Enums\AvailabilityPeriods;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_availabilities', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->primary('user_id');

            $availabilities = AvailabilityPeriods::getHourPartValues();

            $table->set('day_monday', $availabilities);
            $table->set('day_tuesday', $availabilities);
            $table->set('day_wednesday', $availabilities);
            $table->set('day_thursday', $availabilities);
            $table->set('day_friday', $availabilities);
            $table->set('day_saturday', $availabilities);
            $table->set('day_sunday', $availabilities);

            $table->tinyInteger('num_mondays')->unsigned()->default(0);
            $table->tinyInteger('num_tuesdays')->unsigned()->default(0);
            $table->tinyInteger('num_wednesdays')->unsigned()->default(0);
            $table->tinyInteger('num_thursdays')->unsigned()->default(0);
            $table->tinyInteger('num_fridays')->unsigned()->default(0);
            $table->tinyInteger('num_saturdays')->unsigned()->default(0);
            $table->tinyInteger('num_sundays')->unsigned()->default(0);

            $table->text('comments')->nullable();

            $table->timestamps();
        });
    }
};
