<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            // These are no longer needed
//            $table->dropForeign('reports_report_submitted_user_id_foreign');
//            $table->dropForeign('reports_shift_id_foreign');
//            // Won't be using these going forward. Instead, we'll be using the metadata column
//            $table->bigInteger('report_submitted_user_id')->unsigned()->nullable()->change();
//            $table->bigInteger('shift_id')->unsigned()->nullable()->change();
            // SINCE THERE AREN'T MANY INSTALLATIONS, DO THIS MANUALLY FOR THE NEXT INSTALL
            // SO WE DON'T NEED TO INSTALL doctrine/dbal

            $table->json('metadata')->nullable()->after('comments');
        });
    }
};
