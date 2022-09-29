<?php

namespace App\Http\Controllers;

use App\Models\SurveyAnswer;
use Illuminate\Http\Request;

class SurveyAnswerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $latestAnswers = SurveyAnswer::query()
            ->join('surveys', 'survey_answers.survey_id', '=', 'surveys.id')
            ->where('surveys.user_id', $user->id)
            ->orderBy('end_date', 'DESC')
            ->getModels('survey_answers.*');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SurveyAnswer  $surveyAnswer
     * @return array
     */
    public function show(Request $request)
    {
        $user = $request->user();
        $surveyAnswer =
        $totalSurveyAnswers = SurveyAnswer::query()
            ->join('surveys', 'survey_answers.survey_id', '=', 'surveys.id')
            ->where('surveys.user_id', $user->id)
            ->where('surveys.user_id', $surveyAnswer->id)
            ->orderBy('end_date', 'DESC')
            ->getModels('survey_answers.*');

        return [
            'totalSurveyAnswers' => $totalSurveyAnswers,
        ];
    }

}
