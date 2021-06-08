<?php

namespace Modules\Accounts\Http\Controllers\Company;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller;
use Modules\Accounts\Entities\Company\Company;
use Modules\Accounts\Entities\Company\CompanyRole;
use Modules\Accounts\Entities\Role\RoleScope;
use Modules\Accounts\Transformers\Role\RoleResource;

class CompaniesRolesController extends Controller
{
    public function index($company){
        $company = Company::findOrFail($company);
        if(auth('api')->user()->cannot('viewAny', [CompanyRole::class,$company])){
            abort(403);
        }
        return RoleResource::collection($company->roles);
    }

    public function update($company){
        $company = Company::findOrFail($company);
        $rolesData = $this->validateRequest(request ());
        $user = auth('api')->user()->user;
        if(auth('api')->user()->cannot('update', [CompanyRole::class])){
            abort(403);
        }
        foreach ($rolesData as $role){
            CompanyRole::firstOrCreate(['company_id' => $company->id,'role_id' => $role['role_id']]);
            $scopes_of_role = RoleScope::where('company_id', $user->company_id)->where('role_id', $role['role_id'])->get();
            foreach ($scopes_of_role as $role_scope) {
                RoleScope::create([
                    'company_id' => $company->id,
                    'role_id' => $role['role_id'],
                    'scope_id' => $role_scope->scope_id,
                ]);
            }
        }
        Log::notice('Successfully updated roles of a company', ['user_id' => auth ()->user ()->user->id,
            'username' => auth ()->user ()->getHashedUsername(auth ()->user ()->username),
            'ip' => request ()->ip (),
            'target_company_id' => $company->id,
            'roles' => $rolesData,
        ]);
        return RoleResource::collection($company->roles);
    }

    public function show($company){
        $company = Company::findOrFail($company);
        $rolesData = $this->validateRequest(request ());
        request()->request->add(['company_id' => $company->id]);

        if(auth('api')->user()->cannot('viewAny', [CompanyRole::class, $company])){
            abort(403);
        }
        $role = $company->roles()->where('role_id', $rolesData[0]['role_id'])->first();
        return (new RoleResource($role))
            ->response ()
            ->setStatusCode (Response::HTTP_OK);
    }

    public function destroy($company){
        $company = Company::findOrFail($company);
        $rolesData = $this->validateRequest(request ());
        if(auth('api')->user()->cannot('update', [CompanyRole::class])){
            abort(403);
        }
        foreach ($rolesData as $role){
            $company_role = CompanyRole::where('company_id', $company->id)
                ->where('role_id', $role['role_id'])
                ->firstOrFail();
            $company_role->delete();
        }

        Log::notice('Successfully destroyed roles of a company', ['user_id' => auth ()->user ()->user->id,
            'username' => auth ()->user ()->getHashedUsername(auth ()->user ()->username),
            'ip' => request ()->ip (),
            'target_company_id' => $company->id,
            'roles' => $rolesData,
        ]);
        return RoleResource::collection($company->roles);
    }

    private function validateRequest(Request $request)
    {
        $rolesData =  $request->validate ([
            'roles' => 'required|array|min:1',
            'roles.*.role_id' => 'required|exists:roles,id'
        ]);
        //remove duplicate values from array
        $rolesData = array_map("unserialize", array_unique(array_map("serialize", $rolesData['roles'])));
        //remove duplicate values from array
        return $rolesData;
    }
}
