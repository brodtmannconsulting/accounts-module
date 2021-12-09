<?php

namespace Modules\Accounts\Entities\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Modules\Accounts\Database\factories\User\UserFactory;
use Modules\Accounts\Entities\Company\Company;
use Modules\Accounts\Entities\Credential\Credential;
use Modules\Accounts\Entities\Role\Role;
use Modules\Accounts\Entities\Role\UserRole;
use Modules\Accounts\Entities\Scope\Scope;
use Modules\Notification\Entities\NotificationSetting;

class User extends Model
{
    use HasFactory, Notifiable, SoftDeletes;
    const yes = 1;
    const no = 0;

    protected static function newFactory()
    {
        return UserFactory::new();
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
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'allow_log_in' => self::yes,
    ];

    protected $casts = [
        'notification_channels' => 'array'
    ];

    /**
     * Boot function for using with User Events
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model)
        {
            $model->setIdAttribute();
            $model->setNotificationChannels();
        });

        static::deleting(function ($model)
        {
            $model->deleteCredentials();
            $model->deleteRsaKeys();
            $model->deleteUsersRoles();
        });
    }

    public function setIdAttribute() {
        $this->attributes['id'] = Str::random (32);
    }

    public function setLastNameAttribute($last_name){
        $this->attributes['last_name'] = encrypt ($last_name);
    }

    public function setFirstNameAttribute($first_name){
        $this->attributes['first_name'] = encrypt ($first_name);
    }

    public function setNotificationChannels () {
        $this->attributes['notification_channels'] = json_encode(array('mail' => false, 'database' => true));
    }

    public function deleteUsersRoles() {
        $this->roles()->each (function ($role){
            UserRole::where('user_id',$this->id)->where('role_id',$role->id)->delete();
        });
    }

    public function deleteCredentials(){
        $this->credentials()->delete();
    }

    public function deleteRsaKeys(){
        $this->rsa_keys()->delete();
    }

    public function getFullNameAttribute() {
        return ucfirst(decrypt($this->first_name)) . ' ' . ucfirst(decrypt($this->last_name));
    }

    /**
     * @param array $scopes
     * @return bool
     */
    public function hasScopes(array $scopes) {
        $found = true;
        foreach($scopes as $scope) {
            if (!in_array($scope['scope_id'], $this->getScopesIds())) {
                $found = false;
                break;
            }
        }
        return $found;
    }

    /**
     *
     */
    public function setRsaKey(){
        UserRsaKey::create(['user_id' => $this->attributes['id']]);
    }

    public function company(){
        return $this->belongsTo (Company::class);
    }

    public function credentials(){
        return $this->hasMany (Credential::class);
    }

    public function roles(){
        return $this->belongsToMany (Role::class,'user_roles');
    }

    public function rsa_keys(){
        return $this->hasMany (UserRsaKey::class);
    }

    /**
     * @return bool
     */
    public function isUser(){
        return $this->roles()->where('name','user')->exists();
    }

    /**
     * @return bool
     */
    public function isAdmin(){
        return $this->roles()->where('name','admin')->exists();
    }

    /**
     * @return bool
     */
    public function isSuperUser(){
        return $this->roles()->where('name','super_user')->exists();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getScopesCollection(){
        $scopes = collect([]);
        foreach ($this->roles as $role){
            foreach ($role->scopes as $scope){
                $scopes->push($scope);
            }
        }
        return $scopes;
    }

    /**
     * @return array
     */
    public function getScopes(){
        return $this->getScopesNames();
        //return $scopes_names;
    }

    /**
     * @return mixed
     */
    public function getScopesForLogin() {
        $scopes_names = $this->getScopesNames();
        return Scope::makeStringOfScopeNamesArray($scopes_names);
    }


    public function path(){
        return '/users/'. $this->id;
    }

    /**
     * @return array
     */
    public function getScopesNames()
    {
        $scopes_names = [];
        foreach ($this->roles as $role){
            foreach ($role->scopes as $scope){
                array_push($scopes_names,$scope->name);
            }
        }
        return array_unique ($scopes_names);
    }

    /**
     * @return array
     */
    public function getScopesIds()
    {
        $scopes_ids = [];
        foreach ($this->roles as $role){
            foreach ($role->scopes as $scope){
                array_push($scopes_ids,$scope->id);
            }
        }
        return array_unique ($scopes_ids);
    }

    public function getNotificationSettings(): array
    {
        $notificationSettings = NotificationSetting::where('user_id', $this->id)->first();
        $notificationTypes = $notificationSettings->notification_types;
        $regularityOfReceipt = $notificationSettings->regularity_of_receipt;
        $result['notificationTypes'] = $this->getArrayKeys($notificationTypes);
        $result['regularityOfReceipt'] = $this->getArrayKeys($regularityOfReceipt);
        return $result;
    }

    private function getArrayKeys(array $array): array
    {
        $result = array_search(true, $array);
        if ($result != false) $result = array_keys($array, true);
        else $result = [];
        return $result;
    }
}
