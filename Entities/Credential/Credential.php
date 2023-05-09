<?php

namespace Modules\Accounts\Entities\Credential;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;
use Modules\Accounts\Entities\User\User;

class Credential extends Authenticatable
{
    use HasFactory;

    protected $guarded = [];

    protected static function newFactory()
    {
        return \Modules\Accounts\Database\factories\Credential\CredentialFactory::new();
    }

    use HasApiTokens, Notifiable, SoftDeletes;
    protected $validUntil;
    /**
     * The attributes that CANNOT be mass assigned
     *
     * @var array
     */

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

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
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
    ];

    /**
     * Boot function for using with Credential Events
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model)
        {
            $model->setIdAttribute();
            $model->setValidFromAttribute();
            $model->setValidUntilAttributeIfNull();
        });
    }

    public static function getUsernameType($username)
    {
        if(filter_var($username, FILTER_VALIDATE_EMAIL)){
            return $username_type = 'email';
        }else{
            return $username_type = 'username';
        }
    }

    public function setIdAttribute() {
        $this->attributes['id'] = Str::random (32);
    }

    public function setPasswordAttribute($password) {
        $this->attributes['password'] = Hash::make ($password);
    }

    public function setUsernameAttribute($username) {
        $this->attributes['username'] = self::getHashedUsername($username);
        $this->attributes['AES_256_username'] = encrypt ($username);
        $this->attributes['username_type'] = self::getUsernameType($username);
    }

    public function setValidFromAttribute(){
        $this->attributes['valid_from'] = now ();
    }

    public function setValidUntilAttributeIfNull(){
        $this->attributes['valid_until'] = now()->addYear ();
    }

    public function setValidUntilAttribute($valid_until){
        $this->attributes['valid_until'] = Carbon::parse ($valid_until)->toDateTime ();
    }


    public function user(){
        return $this->belongsTo (User::class);
    }


    public function findForPassport($username)
    {
        return $this->where('username', $username)->first();
    }

    public static function getHashedUsername($username)
    {
        return hash("sha512",$username);
    }

    public function path(){
        return '/credentials/'. $this->id;
    }
}
