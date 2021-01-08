<?php

namespace Modules\Accounts\Entities\Scope;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Accounts\Database\factories\Scope\ScopeFactory;

class Scope extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return ScopeFactory::new();
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

//    public static function getScopesNamesByIds(array $scopes_ids): string
//    {
//        $scopes = Scope::whereIn('id',$scopes_ids)->get();
//        return self::makeStringOfScopeNamesArray($scopes);
//    }

    /**
     * @param array $scopes
     * @return string
     */
    public static function makeStringOfScopeNamesArray(array $scopes)
    {
        $scopes_names = '';
        for($i = 0; $i < sizeof ($scopes); $i++){
            if($i < sizeof ($scopes) - 1){
                $scopes_names .= $scopes[$i] .' ';
            }else{
                $scopes_names .= $scopes[$i];
            }
        }
        return $scopes_names;
    }

    /**
     * @return array
     */
    public static function getAllScopesAsArray()
    {
        $scopes = array();
        $scopes_names = Scope::all()->pluck (['name'])->toArray ();
        $scopes_description = Scope::all()->pluck (['description'])->toArray ();
        $scopes = array_merge ($scopes,array_fill_keys($scopes_names, $scopes_description));

        $i = 0;
        $result_scopes = array();
        foreach ($scopes as $scope => $value){
            $test_res = [$scope => $scopes_description[$i]];
            $result_scopes = array_merge($result_scopes,$test_res);
            $i++;
        }

        return $result_scopes;
    }

    /**
     *
     */
    public function getName(){
        $this->name;
    }

    public function path(){
        return '/scopes/'. $this->id;
    }
}
