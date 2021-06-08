<?php

namespace Modules\Accounts\Http\Controllers\User;
use Illuminate\Routing\Controller;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\Accounts\Entities\Company\Company;
use Modules\Accounts\Entities\Credential\Credential;
use Modules\Accounts\Entities\User\User;
use Modules\Accounts\Transformers\User\UserResource;

class UsersController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function indexCompany($company_id)
    {
        $company = Company::findOrFail($company_id);
        if(auth('api')->user()->cannot('viewAnyOfCompany',[User::class, $company])){
            abort(403);
        }
        $users = User::where('company_id',$company->id)->get();
        return UserResource::collection ($users);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        if(auth('api')->user()->cannot('viewAny',User::class)){
            abort(403);
        }
        return UserResource::collection (User::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|object
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request)
    {

        //Validation Part
        $userData = $this->validateUserData($request);
        $credentialsData = $this->validateCredentialsData($request);
        if(auth('api')->user()->cannot('create',User::class)){
            abort(403);
        }

        //Store Part
        $user = User::create($userData);
        foreach ($credentialsData as $credentialData){
            unset($credentialData['password_confirmation']);
            $user->credentials()->create($credentialData);
        }

        Log::notice('Successfully created the user', ['user_id' => auth ()->user ()->user->id,
            'username' => auth ()->user ()->getHashedUsername(auth ()->user ()->username),
            'ip' => request ()->ip (),
            'new_user_id' => $user->id
        ]);

        return (new UserResource($user))
            ->response ()
            ->setStatusCode (Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|object
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show($user)
    {
        $user = User::findOrFail($user);
        if(auth('api')->user()->cannot('view',$user)){
            abort(403);
        }
        return (new UserResource($user))
            ->response ()
            ->setStatusCode (Response::HTTP_OK);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $user
     * @return \Illuminate\Http\JsonResponse|object
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, $user)
    {
        $user = User::findOrFail($user);
        if(auth('api')->user()->cannot('update',$user)){
            abort(403);
        }

        $user->update($this->validateUserDataForUpdate($request));
        $user->fresh();

        Log::notice('Successfully updated the user', ['user_id' => auth ()->user ()->user->id,
            'username' => auth ()->user ()->getHashedUsername(auth ()->user ()->username),
            'ip' => request ()->ip (),
            'updated_user_id' => $user->id
        ]);
        return (new UserResource($user))
            ->response ()
            ->setStatusCode (Response::HTTP_OK);
    }

    /**
     * Update the allow_log_in resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $user
     * @return \Illuminate\Http\JsonResponse|object
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function updateAllowLogIn(Request $request, $user)
    {
        $user = User::findOrFail($user);
        if(auth('api')->user()->cannot('updateAllowLogIn',$user)){
            abort(403);
        }

        $user->update($this->validateUserDataForUpdateAllowLogIn($request));
        $user->fresh();

        Log::notice('Successfully updated the user allowLogIn', ['user_id' => auth ()->user ()->user->id,
            'username' => auth ()->user ()->getHashedUsername(auth ()->user ()->username),
            'ip' => request ()->ip (),
            'updated_user_id' => $user->id
        ]);

        return (new UserResource($user))
            ->response ()
            ->setStatusCode (Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse|object
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy($user)
    {
        $user = User::findOrFail($user);
        if(auth('api')->user()->cannot('delete',$user)){
            abort(403);
        }
        $user->delete();

        Log::notice('Successfully deleted the user', ['user_id' => auth ()->user ()->user->id,
            'username' => auth ()->user ()->getHashedUsername(auth ()->user ()->username),
            'ip' => request ()->ip (),
            'deleted_user' => $user->id
        ]);
        return (new UserResource($user))
            ->response ()
            ->setStatusCode (Response::HTTP_OK);
    }

    private function validateUserData(Request $request)
    {
        return $request->validate ([
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'nullable|max:255|email',
            'language' => 'max:255|in:de,en',
            'company_id' => 'required|exists:companies,id',
            'allow_log_in' => 'boolean',
        ]);
    }

    private function validateUserDataForUpdate(Request $request)
    {
        return $request->validate ([
            'first_name' => 'max:255',
            'last_name' => 'max:255',
            'email' => 'max:255|email',
            'language' => 'max:255|in:de,en',
        ]);
    }

    private function validateUserDataForUpdateAllowLogIn(Request $request)
    {
        return $request->validate ([
            'allow_log_in' => 'boolean',
        ]);
    }

    private function validateCredentialsData(Request $request)
    {
        if (request()->input('credentials') && !empty(request()->input('credentials'))) {
            $credentials_array = request()->input('credentials');

            foreach ($credentials_array as &$credential){
                if (isset($credential['username']) && !empty($credential['username'])) $credential['hashed_username'] = Credential::getHashedUsername($credential['username']);
            }
            request()->merge([
                'credentials' => $credentials_array,
            ]);
        }

        $customMessages = [
            'credentials.*.password.regex'   => 'The :attribute is invalid, password must contain at least one uppercase letter and one number',
            'credentials.*.hashed_username.unique'   => 'This username has already been taken',
        ];
        $credentialsData = $request->validate ([
            "credentials" => "required|array|min:1",
            'credentials.*.username' => 'required|max:255',
            'credentials.*.hashed_username' => 'required|unique:credentials,username',
            'credentials.*.password' => ['required',
                'required_with:credentials.*.password_confirmation',
                'same:credentials.*.password_confirmation',
                'min:8',
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',
                'max:255'],
            'credentials.*.password_confirmation' => 'required|min:8',
        ], $customMessages);

        foreach ($credentialsData['credentials'] as &$credential) {
            unset($credential['hashed_username']);
        }
        return $credentialsData['credentials'];
    }
}
