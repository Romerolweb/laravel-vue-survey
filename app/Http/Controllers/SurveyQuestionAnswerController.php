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
     * @return \Illuminate\Support\Collection
     */
    public function index(Request $request)
    {
        $user = $request->user();

        /**
         * base sql
         *
         *
        select survey_question_answers.id as id, survey_question_answers.survey_question_id, survey_question_answers.survey_answer_id,
        survey_question_answers.answer, survey_question_answers.created_at, survey_answers.id, survey_answers.end_date, survey_answers.survey_id, surveys.user_id
        from `survey_question_answers`
        inner join `survey_answers` on `survey_question_answers`.`id` = `survey_answers`.`id`
        inner join `surveys` on `survey_answers`.`survey_id` = `surveys`.`id`
        where `surveys`.`user_id` = ?
        order by `end_date`
        desc;
         */
        $surveyQuestionAnswers = SurveyQuestionAnswer::query()
            ->join('survey_answers', 'survey_question_answers.id', '=', 'survey_answers.id')
            ->join('surveys', 'survey_answers.survey_id', '=', 'surveys.id')
            ->where('surveys.user_id', 2)
            ->orderBy('end_date', 'DESC')
            ->select('*')
        ->get();
//  ->getModels('survey_question_answers.*');

//            ->get('*')->dd();

        return collect($surveyQuestionAnswers);
//        return (json_decode($surveyQuestionAnswers));
//        return new SurveyQuestionAnswerResource($surveyQuestionAnswers);
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
        /**
         *
        select *
        from survey_question_answers as sqa
        inner join survey_answers sa on sqa.survey_answer_id = sa.id
        inner join survey_questions sq on sqa.survey_question_id = sq.id
        where sa.survey_id = {parameter};
         *
         */
        $surveyAnswers = SurveyQuestionAnswer::query()
            ->join('survey_answers', 'survey_question_answers.id', '=', 'survey_answers.survey_answer_id')
            ->where('surveys.id', $survey->id)
            ->orderBy('end_date', 'DESC')
            ->getModels('survey_answers.*');


        return new SurveyQuestionAnswerResource($surveyAnswers);
    }

}
