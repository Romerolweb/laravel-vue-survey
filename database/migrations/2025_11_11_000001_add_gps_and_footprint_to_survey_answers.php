<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Note: The "2025_11_11" timestamp in this migration's filename is intentional
 * and does not reflect a real calendar date. Migration files are executed in
 * chronological order based on their timestamp prefix. This particular date was
 * chosen to ensure this migration runs after all existing migrations while
 * maintaining a predictable ordering for GPS and footprint feature additions.
 */
class AddGpsAndFootprintToSurveyAnswers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('survey_answers', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->after('end_date');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->decimal('calculated_footprint', 15, 4)->nullable()->after('longitude');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('survey_answers', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'calculated_footprint']);
        });
    }
}
