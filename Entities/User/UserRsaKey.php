<?php

namespace Modules\Accounts\Entities\User;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Accounts\Database\factories\User\UserRsaKeyFactory;

class UserRsaKey extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return UserRsaKeyFactory::new();
    }

    use SoftDeletes;

    protected $fillable = ['user_id'];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['private_key'];

    protected $dates = ['valid_from','valid_until'];

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
            $model->setValidFromAttribute();
            $model->storeNewkeys();
        });
    }

    public function setIdAttribute() {
        $this->attributes['id'] = Str::random (32);
    }

    public function setValidFromAttribute(){
        $this->attributes['valid_from'] = now();
    }

    public function setValidUntilAttribute($valid_until){
        $this->attributes['valid_until'] = Carbon::parse ($valid_until)->toDateTime ();
    }

    /**
     * Generates a new 2048-bit RSA Key-Pair used for various User Activities
     *
     * @return bool returns true if successful. false on failure.
     */
    protected function storeNewkeys()
    {
        $pk_res = openssl_pkey_new( array(
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA
        ));

        openssl_pkey_export($pk_res, $this->attributes['private_key']);

        $pubkey = openssl_pkey_get_details($pk_res);
        $this->attributes['public_key'] = $pubkey["key"];

        openssl_pkey_free($pk_res);

        if( is_null($this->attributes['private_key']) || is_null($this->attributes['public_key']) )
            return false;
        else
            return true;
    }

    public function user(){
        return $this->belongsTo (User::class);
    }
}
