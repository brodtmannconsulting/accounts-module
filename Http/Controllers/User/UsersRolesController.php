<?php

namespace Modules\Accounts\Http\Controllers\User;
use Illuminate\Routing\Controller;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\Accounts\Entities\Company\Company;
use Modules\Accounts\Entities\Role\Role;
use Modules\Accounts\Entities\Role\RoleScope;
use Modules\Accounts\Entities\Role\UserRole;
use Modules\Accounts\Entities\User\User;
use Modules\Accounts\Transformers\User\UserResource;

class UsersRolesController extends Controller
{

    public function store(){
        $user = User::findOrFail(request ()->user_id);
        if(auth('api')->user()->cannot('create',[UserRole::class, $user])){
            abort(403);
        }
        $rolesData = $this->validateRolesData(request (),$user);
        $scopesData = $this->validateScopesData(request ());

        foreach ($rolesData as $roleData){
            $roleData['user_id'] = $user->id;
            UserRole::firstOrCreate($roleData);
        }

        foreach ($scopesData as $scopeData){
            if(isset($scopeData['scopes'])){
                foreach ($scopeData['scopes'] as $scope){
                    $without_scopes_array = $scopeData;
                    unset($without_scopes_array['scopes']);
                    $without_scopes_array['company_id'] = $user->company_id;
                    $roles_scope = RoleScope::firstOrCreate(array_merge($scope, $without_scopes_array));
                }
            }
        }

        Log::notice('Successfully added role to the user', ['user_id' => auth('api')->user ()->user->id,
            'username' => auth('api')->user ()->getHashedUsername(auth('api')->user ()->username),
            'ip' => request ()->ip (),
            'target_user_id' => $user->id,
            'roles' => $rolesData,
        ]);

        //TODO:: LOGOUT (revoked = 1);
        return (new UserResource($user))
            ->response ()
            ->setStatusCode (Response::HTTP_CREATED);
    }

    public function update($role){
        $role = Role::findOrFail($role);
        $users = $this->validateRequestForUpdate(request ());
        if(auth('api')->user()->cannot('update',[UserRole::class, $users])){
            abort(403);
        }
        $user_ids = array();
        foreach ($users as $user){
            array_push ($user_ids, $user['user_id']);
            UserRole::firstOrCreate(['role_id' => $role->id, 'user_id' => $user['user_id']]);
        }


        Log::notice('Successfully updated users roles', ['user_id' => auth('api')->user ()->user->id,
            'username' => auth('api')->user ()->getHashedUsername(auth('api')->user ()->username),
            'ip' => request ()->ip (),
            'target_user_ids' => $user_ids,
            'role_id' => $role->id,
        ]);
        return UserResource::collection ($role->users);
    }

    public function show($role){
        $role = Role::findOrFail($role);
        $data = $this->validateRequest();
        $company = Company::findOrFail($data['company_id']);
        if(auth('api')->user()->cannot('view',[UserRole::class,$role,$company])){
            abort(403);
        }
        $users = $role->users()->where('company_id',$company->id)->get();
        return UserResource::collection ($users);
    }

    public function destroy(){
        $user = User::findOrFail(request ()->user_id);
        if(auth('api')->user()->cannot('delete',[UserRole::class,$user])){
            abort(403);
        }
        UserRole::where('user_id',$user->id)
            ->where('role_id',request ()->role_id)
            ->first()
            ->delete();

        Log::notice('Successfully removed role from a user', ['user_id' => auth('api')->user ()->user->id,
            'username' => auth('api')->user ()->getHashedUsername(auth('api')->user ()->username),
            'ip' => request ()->ip (),
            'target_user_id' => $user->id,
            'role_id' => request ()->role_id,
        ]);

        return (new UserResource($user))
            ->response ()
            ->setStatusCode (Response::HTTP_OK);
    }

    private function validateRolesData(Request $request, User $user)
    {
        $rolesData = $request->validate ([
            'roles.*.role_id' => 'required|exists:roles,id'
        ]);
        //remove duplicate values from array
        $rolesData = array_map("unserialize", array_unique(array_map("serialize", $rolesData['roles'])));

        //checks if company has access to chosen user role
        $this->checkIfCompanyHasAccessToThisRole($rolesData, $user);

        return $rolesData;
    }

    private function validateRequestForUpdate(Request $request)
    {
        $users = $request->validate ([
            'users' => 'required|array|min:1',
            'users.*.user_id' => 'required|exists:users,id'
        ]);
        //remove duplicate values from array
        $users = array_map("unserialize", array_unique(array_map("serialize", $users['users'])));
        return $users;
    }

    private function validateScopesData(Request $request)
    {
        $scopesData = $request->validate ([
            "roles" => "required|array|min:1",
            "roles.*.scopes" => "array",
            'roles.*.scopes.*.scope_id' => 'required|exists:scopes,id',
        ]);
        //remove duplicate values from array
        $scopesData = array_map("unserialize", array_unique(array_map("serialize", $scopesData['roles'])));
        //remove duplicate values from array
        return $scopesData;
    }

    private function validateRequest()
    {
        return request()->validate ([
            "company_id" => "required|exists:companies,id",
        ]);
    }

    private function checkIfCompanyHasAccessToThisRole(array $rolesData, User $user)
    {
        //checks if company has access to chosen user role
        foreach ($rolesData as $roleData){
            Validator::make($roleData, [
                'role_id' => [
                    'required',
                    Rule::exists('company_roles','role_id')->where(function ($query) use ($user) {
                        $query->where('company_id', $user->company_id);
                    }),
                ],
            ])->validate();
        }
    }
}
