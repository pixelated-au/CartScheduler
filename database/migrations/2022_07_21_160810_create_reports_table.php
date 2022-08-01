<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reports', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_id')->constrained();
            $table->smallInteger('placements_count')->unsigned()->default(0);
            $table->smallInteger('videos_count')->unsigned()->default(0);
            $table->smallInteger('requests_count')->unsigned()->default(0);
            $table->boolean('shift_canceled')->default(false);
            $table->string('comments', 1000)->nullable();
            $table->bigInteger('report_submitted_user_id')->unsigned();
            $table->timestamps();

            $table->foreign('report_submitted_user_id')->references('id')->on('users')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
