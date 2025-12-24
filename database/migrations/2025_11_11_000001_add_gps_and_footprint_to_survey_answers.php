<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Note: The "2025_11_11" timestamp in this migration's filename is intentional.
 * It is used to control migration execution order and does not reflect a real
 * calendar date. The actual creation date may differ.
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
