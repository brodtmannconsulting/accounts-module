<?php

namespace Modules\Accounts\Entities\Company;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Modules\Accounts\Database\factories\Company\CompanyFactory;
use Modules\Accounts\Entities\Role\Role;
use Modules\Accounts\Entities\Role\RoleScope;
use Modules\Accounts\Entities\User\User;
use Modules\Consumption\Entities\Consumption;
use Modules\Question\Entities\Question;
use Modules\Question\Entities\QuestionAnswer;

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

    public function calculateQuestionnaireScore ($question_type_id, $category_id = null) {
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

        $company_consumptions = Consumption::where('company_id',$this->id)->whereDate('created_at', '>=', now()->subMonth(12))->whereDate('created_at' ,'<=', $end_date)->get();
        $total_year_co2_footprint = $company_consumptions->sum('co2_footprint');
        $total_year_co2_sequestration = Consumption::getMonthCO2Sequestration($this) * 12;
        $club_co2_footprint = $total_year_co2_footprint - $total_year_co2_sequestration;

        $score = Consumption::interpolateCertification($club_co2_footprint, Consumption::$avg_club_co2_footprint);
        return round($score * 100, 2);
    }

}
