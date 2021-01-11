<?php

namespace Modules\Accounts\Http\Controllers\Auth;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Accounts\Entities\Auth\AccessToken;
use Modules\Accounts\Transformers\User\UserResource;

class AuthenticatedUserController extends Controller
{
    public function index(){
        $token = AccessToken::getToken();
        return (new UserResource($token->user ()->first ()->user))
            ->response ()
            ->setStatusCode (Response::HTTP_OK);
    }
}
