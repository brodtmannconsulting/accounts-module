<?php

namespace Modules\Accounts\Entities\Auth;

use Firebase\JWT\JWT;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\Token;

class AccessToken extends Model
{

    protected $guarded = [];
    /**
     *
//     * @return Token
    @throws \Illuminate\Auth\Access\AuthorizationException
     */

    public static function getToken(){
        $public_key = TokenKey::where('revoked', 0)->firstOrFail()->public_key;
        //decode JWT token and get Instance of Token
        $tokenArray = explode (' ',request ()->header ('Authorization'));
        if($tokenArray[0] != 'Bearer' OR !isset($tokenArray[0]) OR !isset($tokenArray[1])){
            abort(403, 'No token provided');
        }

        $token = JWT::decode ($tokenArray[1],$public_key,array ('RS256'));
        return Token::find($token->jti);
    }
}
