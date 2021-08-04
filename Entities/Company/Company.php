<?php

namespace Modules\Accounts\Entities\Company;

use Carbon\CarbonPeriod;
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
use Modules\Certification\Entities\InternalCertification;
use Modules\Certification\Entities\TotalScoreProgress;
use Modules\Consumption\Entities\Consumption;
use Modules\Consumption\Entities\TotalProgressConsumption;
use Modules\Dashboard\Entities\DashboardElement;
use Modules\Dashboard\Entities\TotalProgress;
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

        static::creating(function (self $model)
        {
            $model->setIdAttribute();
        });

        static::created(function (self $model)
        {
            $model->storeCompanyVariables();
        });

        static::deleting(function (self $model)
        {
            $model->deleteCompanyUsers();
            $model->deleteCompaniesRoles();
            $model->deleteCompaniesVariables();
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

    public function answers(){
        return $this->hasMany (QuestionAnswer::class, 'related_to');
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
     * @param Carbon|null $start_date
     * @param Carbon|null $end_date
     * @return float
     */
    public function calculateConsumptionScore (Carbon $start_date = null, Carbon $end_date = null): float
    {
        if (is_null($start_date) || is_null($end_date)) {
            $start_date = now()->subMonth(13)->startOfMonth();
            $end_date = now()->subMonth(2)->endOfMonth();
        }

        $co2Footprint = $this->calculateCO2Footprint($start_date, $end_date);
        $total_year_club_footprint = $co2Footprint['co2_footprint_minus_co2_sequestration'];

        $score = Consumption::interpolateCertification($total_year_club_footprint, $this->getClubAverageFootprintMinusSequestrationValue() * (($start_date->diffInDays($end_date) + 1) / 365));

        return round($score * 100, 2);
    }

    /**
     * @param Carbon $start_date
     * @param Carbon $end_date
     * @return array
     */
    public function calculateCO2Footprint(Carbon $start_date, Carbon $end_date): array
    {
        $months = [
            1 => [],
            2 => [],
            3 => [],
            4 => [],
            5 => [],
            6 => [],
            7 => [],
            8 => [],
            9 => [],
            10 => [],
            11 => [],
            12 => [],
        ];

        $date_range = [];

        foreach ($months as $key => &$month) {
            $period = CarbonPeriod::create($start_date->toDateString(), $end_date->toDateString());
            foreach ($period as $date) {
                array_push($date_range, $date->month);
            }
            if (in_array($key, $date_range)) {
                $month = [
                    'co2_footprint' => null,
                    'co2_sequestration' => null,
                    'co2_footprint_minus_co2_sequestration' => null,
                ];
            } else {
                unset($months[$key]);
            }
        }

        $company_consumptions = Consumption::where('company_id',$this->id)->whereDate('end_date', '>=', $start_date)->whereDate('end_date' , '<=' , $end_date)->get();

//        dd($start_date->toDateString(), $end_date->toDateString(), $company_consumptions->count());

        $grouped_consumptions_by_months = $company_consumptions->groupBy(function ($consumption) {
            return $consumption->end_date->month;
        });

        foreach ($grouped_consumptions_by_months as $key => $month_consumptions) {
            $months[$key]['co2_footprint'] = $month_consumptions->sum('co2_footprint');

            if (is_null($months[$key]['co2_footprint'])) {
                $months[$key]['co2_footprint_minus_co2_sequestration'] = $this->getClubAverageFootprintMinusSequestrationValue() / 12 * 1.3;
            } else {
                $month_co2_sequestration = Consumption::getMonthCO2Sequestration($this->id);
                $months[$key]['co2_sequestration'] = $month_co2_sequestration;
                $club_co2_footprint = $months[$key]['co2_footprint'] - $month_co2_sequestration;
                $months[$key]['co2_footprint_minus_co2_sequestration'] = $club_co2_footprint;
            }
        }

        $total_year_club_footprint = 0;
        $total_year_co2_footprint = 0;
        $total_year_co2_sequestration = 0;

        foreach ($months as $month) {
            $total_year_club_footprint += $month['co2_footprint_minus_co2_sequestration'];
            $total_year_co2_footprint += $month['co2_footprint'];
            $total_year_co2_sequestration += $month['co2_sequestration'];
        }

        return [
            'total_co2_footprint' => $total_year_co2_footprint,
            'co2_sequestration' => $total_year_co2_sequestration,
            'co2_footprint_minus_co2_sequestration' => $total_year_club_footprint,
            'each_tree_costs' => Consumption::$each_tree,
            'target' => $this->getClubAverageFootprintMinusSequestrationValue(),
        ];
    }

    /**
     * @param Carbon $start_date
     * @param Carbon $end_date
     * @return float
     */
    public function calculateTotalCertificationScore (Carbon $start_date, Carbon $end_date): float {

        $total_score_for_each_block = $this->getTotalScoreForEachBlock($start_date, $end_date);

        $sustainability_score = $total_score_for_each_block['sustainability_score'];
        $resiliency_score = $total_score_for_each_block['resiliency_score'];
        $consumption_score = $total_score_for_each_block['consumption_score'];
        $initiatives_and_engagement_score = $total_score_for_each_block['initiatives_and_engagement_score'];


        $achieved_score_for_each_block = $this->getAchievedScoreForEachBlock($sustainability_score, $resiliency_score, $consumption_score, $initiatives_and_engagement_score);
        $achieved_sustainability_score = $achieved_score_for_each_block ['achieved_sustainability_score'];
        $achieved_resiliency_score = $achieved_score_for_each_block ['achieved_resiliency_score'];
        $achieved_initiatives_and_engagement_score = $achieved_score_for_each_block ['achieved_initiatives_and_engagement_score'];
        $achieved_consumption_score = $achieved_score_for_each_block ['achieved_consumption_score'];

        $total_score = $achieved_sustainability_score + $achieved_resiliency_score + $achieved_initiatives_and_engagement_score + $achieved_consumption_score;

        TotalScoreProgress::firstOrCreate([
            'value' => $total_score,
            'company_id' => $this->id,
            'created_at' => now()->toDateString(),
        ]);

        return round($total_score, 2);
    }

    /**
     * @param Carbon $date
     * @return float
     */
    private function getSustainabilityScore(Carbon $date): float
    {
        $question_type = QuestionType::where('name', 'SignUp')->first();
        return $this->getQuestionnaireTotalScore($question_type, $date);
    }

    /**
     * @param Carbon $date
     * @return float
     */
    private function getResiliencyScore(Carbon $date): float
    {
        $question_type = QuestionType::where('name', 'Resiliency')->first();
        return $this->getQuestionnaireTotalScore($question_type, $date);
    }

    /**
     * @param QuestionType $question_type
     * @param Carbon $date
     * @return float|int
     */
    public function getQuestionnaireTotalScore(QuestionType $question_type, Carbon $date): float
    {
        $total_progress = TotalProgress::where('company_id', $this->id)
            ->where('question_type_id', $question_type->id)
            ->where('updated_at', '<=', $date)
            ->orderBy('updated_at', 'DESC')
            ->first();

        if (is_null($total_progress)) $value = 0;
        else $value = $total_progress->value;
        return $value;
    }


    /**
     * @return InternalCertification | null
     */
    public function getCurrentCertificate (): ?InternalCertification
    {
        $total_score = $this->calculateTotalCertificationScore(now()->subYear(), now());
        $internal_certificates = InternalCertification::where('certification_weight', '<=', $total_score)->with('certification')->get();
        return $internal_certificates->where('certification_weight', $internal_certificates->max('certification_weight'))->first();
    }

    /**
     * @return InternalCertification | null
     */
    public function getNextCertificate () : ?InternalCertification
    {
        $total_score = $this->calculateTotalCertificationScore(now()->subYear(), now());
        $internal_certificates = InternalCertification::where('certification_weight', '>=', $total_score)->with('certification')->get();
        return $internal_certificates->where('certification_weight', $internal_certificates->min('certification_weight'))->first();
    }

    /**
     * @return Collection
     */
    public function getAllAchievedInternalCertificates () : Collection
    {
        $total_score = $this->calculateTotalCertificationScore(now()->subYear(), now());
        return InternalCertification::where('certification_weight', '<=', $total_score)->with('certification')->get();
    }

    /**
     * @param Carbon $start_date
     * @param Carbon $end_date
     * @return array
     */
    public function getTotalScoreForEachBlock(Carbon $start_date, Carbon $end_date): array
    {
        $initiative_question_type = QuestionType::where('name', 'Initiative')->first();
        return [
            'sustainability_score' => $this->getSustainabilityScore($end_date),
            'resiliency_score' => $this->getResiliencyScore($end_date),
            'consumption_score' => $this->calculateConsumptionScore($start_date, $end_date),
            'initiatives_and_engagement_score' => $this->getQuestionnaireTotalScore($initiative_question_type, $end_date)
        ];
    }

    /**
     * @param float $sustainability_score
     * @param float $resiliency_score
     * @param float $initiatives_and_engagement_score
     * @param float $consumption_score
     * @return float[]|int[]
     */
    public function getAchievedScoreForEachBlock(float $sustainability_score, float $resiliency_score, float $initiatives_and_engagement_score, float $consumption_score): array
    {
        $certification_variables = $this->certificationVariables;
        return [
            'achieved_sustainability_score' => $certification_variables->sustainability_certification_volume * ($sustainability_score / 100),
            'achieved_resiliency_score' => $certification_variables->resiliency_certification_volume * ($resiliency_score / 100),
            'achieved_initiatives_and_engagement_score' => $certification_variables->initiatives_and_engagement_volume * ($initiatives_and_engagement_score / 100),
            'achieved_consumption_score' => $certification_variables->consumption_certification_volume * ($consumption_score / 100),
        ];
    }

    /**
     *
     */
    private function storeCompanyVariables (): void {
        CompanyCertificationVariables::create([
            'company_id' => $this->id
        ]);
    }

    /**
     *
     */
    private function deleteCompaniesVariables(): void
    {
        CompanyCertificationVariables::where('company_id', $this->id)->delete();
    }

    public function saveConsumptionProgress($score): TotalProgressConsumption {
        return TotalProgressConsumption::firstOrCreate([
            'value' => $score,
            'company_id' => $this->id,
            'created_at' => now()->toDateString(),
        ]);
    }

    private function getRoundsOfGolfPlayed()
    {
        $result = Consumption::$avg_club_rounds_of_golf_played;
        $question = Question::where('old_id', 'JU6VAXH5V7S4FUSRD4XB')->first();
        $answer = $question->companyQuestionAnswer($this)->first();
        if ($answer) $result = $answer->value;
        return $result;
    }

    public function getClubAverageFootprintMinusSequestrationValue()
    {
        return $this->getRoundsOfGolfPlayed() * 12 / Consumption::$avg_club_rounds_of_golf_played * Consumption::$avg_club_co2_footprint;
    }

}
