<?php

namespace Modules\Accounts\Entities\Company;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\Accounts\Database\factories\Company\CompanyFactory;
use Modules\Accounts\Entities\Role\Role;
use Modules\Accounts\Entities\Role\RoleScope;
use Modules\Accounts\Entities\User\User;
use Modules\Category\Entities\Category;
use Modules\Certification\Entities\CompanyCertificationVariables;
use Modules\Certification\Entities\TotalScoreProgress;
use Modules\Consumption\Entities\Consumption;
use Modules\Dashboard\Entities\DashboardElement;
use Modules\Question\Entities\Question;
use Modules\Question\Entities\QuestionAnswer;
use Modules\Question\Entities\QuestionType;

;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected static function newFactory()
    {
        return CompanyFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';



    /**
     * Boot function for using with Company Events
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model)
        {
            $model->setIdAttribute();
        });

        static::deleting(function ($model)
        {
            $model->deleteCompanyUsers();
            $model->deleteCompaniesRoles();
        });
    }

    public function setIdAttribute() {
        $this->attributes['id'] = Str::random (32);
    }

    public function deleteCompanyUsers() {
        $this->users()->delete ();
    }

    public function deleteCompaniesRoles() {
        RoleScope::where('company_id',$this->id)->delete ();
        $this->roles()->where ('is_custom',1)->delete ();
        CompanyRole::where('company_id',$this->id)->delete();
    }

    public function users(){
        return $this->hasMany (User::class);
    }

    /**
     * The roles that belong to the company.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class,'company_roles','company_id','role_id');
    }

    /**
     * The credentials that belong to the company users.
     */
    public function credentials()
    {
        $credentials = collect([]);
        $this->users ()->each (function ($user) use($credentials){
            $user->credentials()->each(function ($credential) use($credentials){
                $credentials->push($credential);
            });
        });
        $credentials = $credentials->unique();
        return $credentials;
    }


    public function path(){
        return '/companies/'. $this->id;
    }

    public function certificationVariables()
    {
        return $this->hasOne (CompanyCertificationVariables::class);
    }


    /**
     * @param $question_type_id
     * @param null $category_id
     * @return float
     */
    public function calculateQuestionnaireScore ($question_type_id, $category_id = null): float
    {
        $question_ids = Question::where('question_type_id', $question_type_id);
        if ($category_id) $question_ids->where('category_id', $category_id);
        $question_ids = $question_ids->pluck ('id');

        $answers = QuestionAnswer::where('related_to', $this->id)->whereIn('question_id', $question_ids)->with('question')->get();

        $not_skipped_answers = $answers->filter(function ($answer) {
            return $answer->skipped == 0;
        });

        $skipped_answers = $answers->filter(function ($answer) {
            return $answer->skipped == 1;
        });


        $total_sum_question_weights = Question::where('question_type_id', $question_type_id)
            ->whereNotIn('id', $skipped_answers->pluck('question_id'));

        if ($category_id) $total_sum_question_weights->where('category_id', $category_id);
        $total_sum_question_weights = $total_sum_question_weights->sum('question_weight');

        $achieved_sum__weights = $not_skipped_answers->sum('achieved_weight');
        return round($achieved_sum__weights / $total_sum_question_weights,3);
    }

    /**
     * @param Carbon|null $start_date
     * @param Carbon|null $end_date
     * @return float
     */
    public function calculateConsumptionScore (Carbon $start_date = null, Carbon $end_date = null): float
    {
        if (is_null($start_date) || is_null($end_date)) {
            $start_date = now()->subMonth(12);
            $end_date = now();
        }

        $company_consumptions = Consumption::where('company_id',$this->id)->whereDate('created_at', '>=', $start_date)->whereDate('created_at' ,'<=', $end_date)->get();
        $total_year_co2_footprint = $company_consumptions->sum('co2_footprint');
        $total_year_co2_sequestration = Consumption::getMonthCO2Sequestration($this) * 12;
        $club_co2_footprint = $total_year_co2_footprint - $total_year_co2_sequestration;

        $score = Consumption::interpolateCertification($club_co2_footprint, Consumption::$avg_club_co2_footprint);
        return round($score * 100, 2);
    }


    /**
     * @param Carbon|null $start_date
     * @param Carbon|null $end_date
     * @return float
     */
    public function calculateInitiativesAndEngagementScore (Carbon $start_date = null, Carbon $end_date = null): float {

        if (is_null($start_date) || is_null($end_date)) {
            $start_date = now()->subMonth(12);
            $end_date = now();
        }
        $score = 0;
        $users = $this->users;

        $questions = Question::where('suggestion', 1)
            ->whereNotNull('approved_at')
            ->whereIn('created_by', $users->pluck('id'))
            ->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at' ,'<=', $end_date)
            ->get();
        $company_certifications_vars = CompanyCertificationVariables::where('company_id', $this->id)->first();
        $score += $questions->count() * $company_certifications_vars->suggestion_points_volume;
        return $score;
    }

    /**
     * @param Carbon|null $start_date
     * @param Carbon|null $end_date
     * @return float
     */
    public function calculateTotalCertificationScore (Carbon $start_date = null, Carbon $end_date = null): float {

        if (is_null($start_date) || is_null($end_date)) {
            $start_date = now()->subMonth(12);
            $end_date = now();
        }

        $sustainability_score = $this->calculateSustainabilityScore();
        $resiliency_score = $this->calculateResiliencyScore();
        $consumption_score = $this->calculateConsumptionScore($start_date, $end_date);
        $initiatives_and_engagement_score = $this->calculateInitiativesAndEngagementScore($start_date, $end_date);

        $certification_variables = $this->certificationVariables;

        $achieved_sustainability_score = $certification_variables->sustainability_certification_volume * ($sustainability_score / 100);
        $achieved_resiliency_score = $certification_variables->resiliency_certification_volume * ($resiliency_score / 100);
        $achieved_initiatives_and_engagement_score = $certification_variables->initiatives_and_engagement_volume * ($initiatives_and_engagement_score / 100);
        $achieved_consumption_score = $certification_variables->consumption_certification_volume * ($consumption_score / 100);


        $total_score = $achieved_sustainability_score + $achieved_resiliency_score + $achieved_initiatives_and_engagement_score + $achieved_consumption_score;

        TotalScoreProgress::firstOrCreate([
            'value' => $total_score,
            'company_id' => $this->id,
            'created_at' => now()->toDateString(),
        ]);

        return round($total_score, 2);
    }

    /**
     * @param Carbon|null $start_date
     * @param Carbon|null $end_date
     * @return float
     */
    private function calculateSustainabilityScore(): float
    {
        $question_type = QuestionType::where('name', 'SignUp')->first();
        return $this->calculateQuestionnaireTotalScore($question_type);
    }

    /**
     * @param Carbon|null $start_date
     * @param Carbon|null $end_date
     * @return float
     */
    private function calculateResiliencyScore(): float
    {
        $question_type = QuestionType::where('name', 'Resiliency')->first();
        return $this->calculateQuestionnaireTotalScore($question_type);
    }

    /**
     * @param QuestionType $question_type
     * @return float|int
     */
    private function calculateQuestionnaireTotalScore(QuestionType $question_type): float
    {
        $score = $this->calculateQuestionnaireScore($question_type->id);

        return round ($score * 100, 2);
    }

}
