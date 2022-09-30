<?php

namespace App\Http\Controllers;

use App\Http\Resources\SurveyQuestionAnswerResource;
use App\Models\Survey;
use App\Models\SurveyQuestionAnswer;
use Illuminate\Http\Request;

class SurveyQuestionAnswerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return SurveyQuestionAnswerResource
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $surveyQuestionAnswers = SurveyQuestionAnswer::query()
            ->join('survey_question_answers', 'survey_answers.id', '=', 'survey_question_answers.survey_answer_id')
            ->join('surveys', 'survey_answers.survey_id', '=', 'surveys.id')
            ->where('surveys.user_id', $user->id)
            ->orderBy('end_date', 'DESC')
            ->getModels('survey_answers.*');

        return new SurveyQuestionAnswerResource($surveyQuestionAnswers);

    }

    /**
     * Display the specified resource.
     *
     * @param Survey $survey
     * @param Request $request
     * @return SurveyQuestionAnswerResource
     */
    public function showBySurveyId(Survey $survey, Request $request): SurveyQuestionAnswerResource
    {
        $user = $request->user();

        $surveyAnswers = SurveyQuestionAnswer::query()
            ->join('survey_question_answers', 'survey_answers.id', '=', 'survey_question_answers.survey_answer_id')
            ->where('surveys.user_id', $user->id)
            ->where('surveys.id', $survey->id)
            ->orderBy('end_date', 'DESC')
            ->getModels('survey_answers.*');

        return new SurveyQuestionAnswerResource($surveyAnswers);
    }

}
