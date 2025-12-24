<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyAnswer extends Model
{
    use HasFactory;

    public const CREATED_AT = null;
    public const UPDATED_AT = null;

    protected $fillable = ['survey_id', 'start_date', 'end_date', 'latitude', 'longitude', 'calculated_footprint'];

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function survey_question_answers(){
        return $this->hasMany(SurveyQuestionAnswer::class);
    }

    public function survey_questions(){
        return $this->hasManyThrough(SurveyAnswer::class, SurveyQuestionAnswer::class);
    }

}
