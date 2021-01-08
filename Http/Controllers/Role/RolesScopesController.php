<?php

namespace Modules\Accounts\Http\Controllers\Role;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Modules\Accounts\Entities\Role\Role;
use Modules\Accounts\Entities\Role\RoleScope;
use Modules\Accounts\Transformers\Role\RoleResource;

class RoleScopeController extends Controller
{
    public function update($role){
        $role = Role::findOrFail($role);
        $scopesData = $this->validateScopesData(request());
        if(auth('api')->user()->cannot('update',[RoleScope::class, $scopesData])){
            abort(403);
        }
        foreach ($scopesData as $scope){
            RoleScope::firstOrCreate([
                'constants_scope_id' => $scope['constants_scope_id'],
                'constants_user_role_id' => $role->id,
                'company_id' => auth ()->user ()->user->company_id
            ]);
        }

        Log::notice('Successfully updated the roles_scopes', ['user_id' => auth ()->user ()->user->id,
            'username' => auth ()->user ()->getHashedUsername(auth ()->user ()->username),
            'ip' => request ()->ip (),
            'role_id' => $role->id
        ]);

        return (new RoleResource($role))
            ->response ()
            ->setStatusCode (Response::HTTP_OK);
    }

    public function destroy($role){
        $role = Role::findOrFail($role);
        $scopesData = $this->validateScopesData(request());
        if(auth('api')->user()->cannot('update',[RoleScope::class, $scopesData])){
            abort(403);
        }
        foreach ($scopesData as $scope){
            $role_scope = RoleScope::where('constants_scope_id', $scope['constants_scope_id'])
                ->where('constants_user_role_id', $role->id)
                ->where('company_id', auth ()->user ()->user->company_id)
                ->firstOrFail();
            $role_scope->delete();
        }

        Log::notice('Successfully deleted the roles_scopes', ['user_id' => auth ()->user ()->user->id,
            'username' => auth ()->user ()->getHashedUsername(auth ()->user ()->username),
            'ip' => request ()->ip (),
            'role_id' => $role->id
        ]);

        return (new RoleResource($role))
            ->response ()
            ->setStatusCode (Response::HTTP_OK);
    }

    private function validateScopesData(Request $request)
    {
        $scopesData = $request->validate ([
            "scopes" => "required|array|min:1",
            'scopes.*.constants_scope_id' => 'required|exists:constants_scopes,id',
        ]);
        //remove duplicate values from array
        $scopesData = array_map("unserialize", array_unique(array_map("serialize", $scopesData['scopes'])));
        //remove duplicate values from array
        return $scopesData;
    }
}
