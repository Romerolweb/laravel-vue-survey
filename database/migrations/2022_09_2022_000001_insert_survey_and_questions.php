<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InsertSurveyAndQuestions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // This migration originally intended to seed data.
        // This responsibility has been moved to SurveyQuestionsSeeder.php
        // to centralize seeding logic and handle the specific "Calculadora Huella Hidrica" survey.
        // Thus, this up() method will now do nothing.
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Since the up() method does nothing, the down() method also does nothing.
        // Any data seeded by SurveyQuestionsSeeder.php should be managed by that seeder
        // or by a general database cleanup/reset process if needed, not this specific migration.
    }
}
