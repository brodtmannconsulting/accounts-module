<?php

namespace Modules\Accounts\Http\Controllers\Role;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\Accounts\Entities\Company\CompanyRole;
use Modules\Accounts\Entities\Role\Role;
use Modules\Accounts\Entities\Role\RoleScope;
use Modules\Accounts\Transformers\Role\RoleResource;

class RolesController extends Controller
{
    public function index(){
        return RoleResource::collection(Role::all ());
    }

    public function store(){
        $roleData = $this->validateRoleDataForStore();
        $scopesData = $this->validateScopeDataForStore();

        if(auth('api')->user()->cannot('create', [Role::class, $scopesData])){
            abort(403);
        }
        //make array unique and make from multidimensional array a single array
        $company_ids = array_map("unserialize", array_unique(array_map("serialize", array_column($scopesData, 'companies_ids'))))[0];

        $role = Role::create($roleData);
        foreach ($company_ids as $company_id){
            CompanyRole::create(['company_id' => $company_id, 'role_id' => $role->id]);
        }

        foreach ($scopesData as $scopeData){
            foreach ($company_ids as $company_id){
                $roles_scope = RoleScope::create([
                    'scope_id' => $scopeData['scope_id'],
                    'role_id' => $role->id,
                    'company_id' => $company_id,
                ]);
            }
        }

        Log::notice('Successfully stored the role', ['user_id' => auth('api')->user ()->user->id,
            'username' => auth('api')->user ()->getHashedUsername(auth('api')->user ()->username),
            'ip' => request ()->ip (),
            'role_id' => $role->id
        ]);


        return (new RoleResource($role))
            ->response ()
            ->setStatusCode (Response::HTTP_CREATED);
    }

    public function show($role){
        $role = Role::findOrFail($role);
        if(auth('api')->user()->cannot('view', $role)){
            abort(403);
        }
        return (new RoleResource($role))
            ->response ()
            ->setStatusCode (Response::HTTP_OK);
    }

    public function update($role){
        $roleData = $this->validateRoleDataForUpdate($role);
        $role = Role::findOrFail($role);
        if(auth('api')->user()->cannot('update', $role)){
            abort(403);
        }
        $role->update($roleData);
        $role->fresh();

        Log::notice('Successfully updated the role', ['user_id' => auth('api')->user ()->user->id,
            'username' => auth('api')->user ()->getHashedUsername(auth('api')->user ()->username),
            'ip' => request ()->ip (),
            'role_id' => $role->id
        ]);

        return (new RoleResource($role))
            ->response ()
            ->setStatusCode (Response::HTTP_OK);
    }

    public function destroy($role){
        $role = Role::findOrFail($role);
        if(auth('api')->user()->cannot('delete', $role)){
            abort(403);
        }
        $role->delete();

        Log::notice('Successfully deleted the role', ['user_id' => auth('api')->user ()->user->id,
            'username' => auth('api')->user ()->getHashedUsername(auth('api')->user ()->username),
            'ip' => request ()->ip (),
            'role_id' => $role->id
        ]);

        return (new RoleResource($role))
            ->response ()
            ->setStatusCode (Response::HTTP_OK);
    }

    private function validateRoleDataForStore()
    {
        if(auth('api')->user ()->tokenCan('system_roles_add_all')){
            return request ()->validate ([
                'name' => 'required|max:255|unique:roles,name',
                'description' => 'required|max:255',
                'is_custom' => 'required|boolean'
            ]);
        }

        return request ()->validate ([
            'name' => 'required|max:255|unique:roles,name',
            'description' => 'required|max:255',
            'is_custom' => 'required|boolean|notIn:0'
        ]);
    }

    private function validateRoleDataForUpdate($role)
    {
        if(auth('api')->user ()->tokenCan('system_roles_add_all')){
            return request ()->validate ([
                'name' => 'max:255|unique:roles,name,'. $role,
                'description' => 'max:255',
                'is_custom' => 'boolean'
            ]);
        }

        return request ()->validate ([
            'name' => 'max:255|unique:roles,name' . $role,
            'description' => 'max:255',
            'is_custom' => 'boolean|notIn:0'
        ]);
    }

    private function validateScopeDataForStore()
    {
        $scopesData = request()->validate ([
            "scopes" => "required|array|min:1",
            'scopes.*.scope_id' => 'required|exists:scopes,id',
            'scopes.*.companies_ids' => "required|array|min:1,exists:companies,id",
        ]);
        //remove duplicate values from array
        $scopesData = array_map("unserialize", array_unique(array_map("serialize", $scopesData['scopes'])));
        //remove duplicate values from array
        return $scopesData;
    }
}
