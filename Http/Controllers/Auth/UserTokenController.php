<?php

namespace Modules\Accounts\Http\Controllers\AuthController;

use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Laravel\Passport\Token;
use Modules\Accounts\Entities\Auth\TokenKey;
use Modules\Accounts\Transformers\Auth\TokenResource;

class UserTokenController extends Controller
{
    public function show(){
        $public_key = TokenKey::where('revoked', 0)->firstOrFail()->public_key;
        $token = JWT::decode (request()->token,$public_key,array ('RS256'));
        $token = Token::find($token->jti);
        return (new TokenResource($token))
            ->response ()
            ->setStatusCode (Response::HTTP_OK);
    }
}
