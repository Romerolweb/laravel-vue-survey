<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SurveyQuestionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $surveyData = [
            'id' => 700,
            'user_id' => 8,
            'image' => "images/PjX55AMIoeFC08Um.jpeg",
            'title' => "Calculadora Huella Hidrica",
            'slug' => "calculadora-huella-hidrica",
            'status' => "1",
            'description' => "Calculadora Huella Hidrica, se realizan preguntas a productores de vino para recolectar informacion.",
            'created_at' =>  Carbon::now()->format('Y/m/d H:i:s'),
            'updated_at' => Carbon::now()->format('Y/m/d H:i:s'),
            'expire_date' => Carbon::now()->format('2030/m/d H:i:s'),
        ];

        $surveyQuestionsData = [
            [
                'type' => "text",
                'question' => "Nombre",
                'description' => "",
                'data' => Carbon::now()->format('Y/m/d H:i:s'),
                'survey_id' => 700,
                'created_at' => Carbon::now()->format('2030/m/d H:i:s'),
                'updated_at' => Carbon::now()->format('2030/m/d H:i:s'),
            ],
            [
                'type' => "text",
                'question' => "¿Qué tipo de fruta o frutas utiliza para la producción de vino?",
                'description' => "",
                'data' => Carbon::now()->format('Y/m/d H:i:s'),
                'survey_id' => 700,
                'created_at' => Carbon::now()->format('2030/m/d H:i:s'),
                'updated_at' => Carbon::now()->format('2030/m/d H:i:s'),
            ],
            [
                'type' => "text",
                'question' => "¿Usted cultiva la fruta con la que elabora el vino?",
                'description' => "",
                'data' => Carbon::now()->format('Y/m/d H:i:s'),
                'survey_id' => 700,
                'created_at' => Carbon::now()->format('2030/m/d H:i:s'),
                'updated_at' => Carbon::now()->format('2030/m/d H:i:s'),
            ],
            [
                'type' => "text",
                'question' => "¿Cuántos litros de vino produce mensualmente?",
                'description' => "",
                'data' => Carbon::now()->format('Y/m/d H:i:s'),
                'survey_id' => 700,
                'created_at' => Carbon::now()->format('2030/m/d H:i:s'),
                'updated_at' => Carbon::now()->format('2030/m/d H:i:s'),
            ],
            [
                'type' => "text",
                'question' => "¿Qué sustancias utiliza para la fermentación alcohólica del vino?",
                'description' => "",
                'data' => Carbon::now()->format('Y/m/d H:i:s'),
                'survey_id' => 700,
                'created_at' => Carbon::now()->format('2030/m/d H:i:s'),
                'updated_at' => Carbon::now()->format('2030/m/d H:i:s'),
            ],
            [
                'type' => "text",
                'question' => "¿Cuántos litros de agua consume mensualmente para la producción de vino?",
                'description' => "",
                'data' => Carbon::now()->format('Y/m/d H:i:s'),
                'survey_id' => 700,
                'created_at' => Carbon::now()->format('2030/m/d H:i:s'),
                'updated_at' => Carbon::now()->format('2030/m/d H:i:s'),
            ],
            [
                'type' => "text",
                'question' => "¿Dónde realiza la descarga de agua residual de la producción de vino?",
                'description' => "",
                'data' => Carbon::now()->format('Y/m/d H:i:s'),
                'survey_id' => 700,
                'created_at' => Carbon::now()->format('2030/m/d H:i:s'),
                'updated_at' => Carbon::now()->format('2030/m/d H:i:s'),
            ],
            [
                'type' => "text",
                'question' => "Si su respuesta fue Otro especificar a continuación especificar",
                'description' => "",
                'data' => Carbon::now()->format('Y/m/d H:i:s'),
                'survey_id' => 700,
                'created_at' => Carbon::now()->format('2030/m/d H:i:s'),
                'updated_at' => Carbon::now()->format('2030/m/d H:i:s'),
            ],
            [
                'type' => "text",
                'question' => "¿Reutiliza el agua que se genera durante la producción del vino?",
                'description' => "",
                'data' => Carbon::now()->format('Y/m/d H:i:s'),
                'survey_id' => 700,
                'created_at' => Carbon::now()->format('2030/m/d H:i:s'),
                'updated_at' => Carbon::now()->format('2030/m/d H:i:s'),
            ],
            [
                'type' => "text",
                'question' => "Si su respuesta fue sí, especificar en qué reutiliza el agua",
                'description' => "",
                'data' => Carbon::now()->format('Y/m/d H:i:s'),
                'survey_id' => 700,
                'created_at' => Carbon::now()->format('2030/m/d H:i:s'),
                'updated_at' => Carbon::now()->format('2030/m/d H:i:s'),
            ],
            [
                'type' => "text",
                'question' => "¿Qué sustancias utiliza para la desinfección de las herramientas y equipos que utiliza para la producción de vino?",
                'description' => "",
                'data' => Carbon::now()->format('Y/m/d H:i:s'),
                'survey_id' => 700,
                'created_at' => Carbon::now()->format('2030/m/d H:i:s'),
                'updated_at' => Carbon::now()->format('2030/m/d H:i:s'),
            ],
            [
                'type' => "text",
                'question' => "¿Usted hace cuántos años produce vino?",
                'description' => "La respeusta solo acepta valores numericos",
                'data' => Carbon::now()->format('Y/m/d H:i:s'),
                'survey_id' => 700,
                'created_at' => Carbon::now()->format('2030/m/d H:i:s'),
                'updated_at' => Carbon::now()->format('2030/m/d H:i:s'),
            ],
        ];


        DB::table('surveys')->insert($surveyData);
        DB::table('survey_questions')->insert($surveyQuestionsData);
    }
}
