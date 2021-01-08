<?php

namespace Modules\Accounts\Entities\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Accounts\Database\factories\Auth\TokenKeyFactory;

class TokenKey extends Model
{
    use HasFactory, SoftDeletes;

    protected static function newFactory()
    {
        return TokenKeyFactory::new();
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
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
            $model->revokeOldKeys();
        });
    }

    private function revokeOldKeys(){
        $old_tokens = $this::where('revoked', 0);
        $old_tokens->update(array("revoked" => "1"));
    }


}
