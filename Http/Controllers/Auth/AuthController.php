<?php

namespace Modules\Accounts\Http\Controllers\Auth;


use Carbon\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Accounts\Entities\Credential\Credential;

class AuthController extends Controller
{
    public function login(){
        $this->validateData ();
        $username = request ()->username;
        $password = request ()->password;
        $hashedUsername = Credential::getHashedUsername($username);
        if(!Auth::guard('web')->attempt(['username' => $hashedUsername, 'password' => $password]) || !$this->credentialsHaveValidDate()) {
            return response()->json([
                'error' => 'invalid_credentials'
            ], 401);
        } else {
            $user = auth('web')->user();
            $tokenResult = $this->getToken($user);
            return response()->json($tokenResult);
        }
    }

    /**
     * @return bool
     */
    private function credentialsHaveValidDate()
    {
        if (is_null (auth()->user()->valid_until) || auth()->user()->valid_until > Carbon::now ()){
            if(auth()->user()->user->allow_log_in == 1){
                return true;
            }
        }
        return false;

    }

    public function getToken($user){
        $tokenResult = $user->createToken('Personal Access Token', $user->user->getScopesNames());
        $token = $tokenResult->token;

        $token->expires_at = Carbon::now()->addWeeks(1);
        $token->save();

        return $tokenResult;
    }

    public function validateData(){
        $customMessages = [
            'g-recaptcha-response.required'  => 'A recaptcha is required!',
        ];
        return request ()->validate ([
            'g-recaptcha-response' => 'required|recaptcha',
            'username' => 'required|max:255',
            'password' => 'required|max:255',
        ],$customMessages);
    }

    public function logout(){
        if(auth('api')->user() != null){
            auth('api')->user()->tokens->each(function($token, $key){
                $token->delete();
            });
            return response()->json(['status' => 'logged_out'], 200);
        }
        else{
            return response()->json(['status' => 'not_logged_in'], 401);
        }
    }
}
