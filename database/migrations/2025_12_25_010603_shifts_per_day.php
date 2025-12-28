<?php

use App\Enums\ShiftsPerDay;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $enumValues = array_column(ShiftsPerDay::cases(), 'value');

        Schema::table('user_availabilities', function (Blueprint $table) use ($enumValues) {
            $table->enum('shifts_monday', $enumValues)->nullable()->after('num_sundays');
            $table->enum('shifts_tuesday', $enumValues)->nullable()->after('shifts_monday');
            $table->enum('shifts_wednesday', $enumValues)->nullable()->after('shifts_tuesday');
            $table->enum('shifts_thursday', $enumValues)->nullable()->after('shifts_wednesday');
            $table->enum('shifts_friday', $enumValues)->nullable()->after('shifts_thursday');
            $table->enum('shifts_saturday', $enumValues)->nullable()->after('shifts_friday');
            $table->enum('shifts_sunday', $enumValues)->nullable()->after('shifts_saturday');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_availabilities', function (Blueprint $table) {
            $table->dropColumn([
                'shifts_monday',
                'shifts_tuesday',
                'shifts_wednesday',
                'shifts_thursday',
                'shifts_friday',
                'shifts_saturday',
                'shifts_sunday',
            ]);
        });
    }
};
