<?php

namespace Modules\Accounts\Http\Controllers\Credential;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Modules\Accounts\Entities\Company\Company;
use Modules\Accounts\Entities\Credential\Credential;
use Modules\Accounts\Entities\User\User;
use Modules\Accounts\Transformers\Credential\CredentialResource;

class CredentialController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     */
    public function index()
    {
        if(auth('api')->user()->cannot('viewAny', Credential::class)){
            abort(403);
        }
        return CredentialResource::collection (Credential::all ());
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     */
    public function indexCompany($company)
    {
        $company = Company::findOrFail($company);
        if(auth('api')->user()->cannot('viewAnyOfCompany', [Credential::class,$company])){
            abort(403);
        }
        return CredentialResource::collection ($company->credentials());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse|object
     * @throws AuthorizationException
     */
    public function store(Request $request)
    {
        $data = $this->validateData();
        $user = User::findOrFail($data['user_id']);
        if(auth('api')->user()->cannot('create', [Credential::class, $user])){
            abort(403);
        }

        $credential = Credential::create($data);

        Log::notice('Successfully stored new credential to a user', ['user_id' => auth ()->user ()->user->id,
            'username' => auth ()->user ()->getHashedUsername(auth ()->user ()->username),
            'ip' => request ()->ip (),
            'target_user_id' => $user->id,
            'credential_id' => $credential->id,
        ]);
        return (new CredentialResource($credential))
            ->response ()
            ->setStatusCode (Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param string $credential
     * @return JsonResponse|object
     * @throws AuthorizationException
     */
    public function show($credential)
    {
        $credential = Credential::findOrFail($credential);
        if(auth('api')->user()->cannot('view', [Credential::class, $credential])){
            abort(403);
        }
        return (new CredentialResource($credential))
            ->response ()
            ->setStatusCode (Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param string $credential
     * @return JsonResponse|object
     * @throws AuthorizationException
     */
    public function update(Request $request, $credential)
    {
        $credential = Credential::findOrFail($credential);
        if(auth('api')->user()->cannot('update', [Credential::class, $credential])){
            abort(403);
        }
        $credential->update($this->validateUpdateData ($credential->id));
        $credential->fresh();

        Log::notice('Successfully updated a credential of a user', ['user_id' => auth ()->user ()->user->id,
            'username' => auth ()->user ()->getHashedUsername(auth ()->user ()->username),
            'ip' => request ()->ip (),
            'credential_id' => $credential->id,
        ]);

        return (new CredentialResource($credential))
            ->response ()
            ->setStatusCode (Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $credential
     * @return JsonResponse|object
     * @throws AuthorizationException
     */
    public function destroy($credential)
    {
        $credential = Credential::findOrFail($credential);
        if(auth('api')->user()->cannot('delete', [Credential::class, $credential])){
            abort(403);
        }
        $credential->delete();

        Log::notice('Successfully destroyed a credential of a user', ['user_id' => auth ()->user ()->user->id,
            'username' => auth ()->user ()->getHashedUsername(auth ()->user ()->username),
            'ip' => request ()->ip (),
            'credential_id' => $credential->id,
        ]);
        return (new CredentialResource($credential))
            ->response ()
            ->setStatusCode (Response::HTTP_OK);
    }

    private function validateData()
    {
        if (request()->input('username')) {
            request()->merge([
                'hashed_username' => Credential::getHashedUsername(request()->input('username')),
            ]);
        }

        $customMessages = [
            'password.regex'   => 'The :attribute is invalid, password must contain at least one lowercase letter, one uppercase letter and one number',
            'hashed_username.unique'   => 'This username has already been taken',
        ];
        $data = request ()->validate ([
            'user_id' => 'required|exists:users,id',
            'username' => 'required|max:255|unique:credentials,username',
            'hashed_username' => 'unique:credentials,username',
            'password' => ['required',
                'min:8',
                'confirmed',
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',
                'max:255'],
            'valid_until' => 'date',
        ], $customMessages);
        unset($data["hashed_username"]);
        return $data;
    }

    private function validateUpdateData($credential_id)
    {
        $customMessages = [
            'password.regex'   => 'The :attribute is invalid, password must contain at least one lowercase letter, one uppercase letter and one number',
            'hashed_username.unique'   => 'This username has already been taken',
        ];

        if (request()->input('username')) {
            request()->merge([
                'hashed_username' => Credential::getHashedUsername(request()->input('username')),
            ]);
        }

        $data = request ()->validate ([
            'user_id' => 'exists:users,id',
            'username' => 'max:255',
            'hashed_username' => 'unique:credentials,username,'. $credential_id,
            'password' => ['min:8',
                'confirmed',
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',
                'max:255'],
            'valid_until' => 'date',
        ], $customMessages);
        unset($data["hashed_username"]);
        return $data;
    }
}
