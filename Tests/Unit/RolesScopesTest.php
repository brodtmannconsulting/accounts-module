<?php

namespace Modules\Accounts\Tests\Unit;

use Illuminate\Support\Facades\Http;
use Modules\Accounts\Entities\Company\Company;
use Modules\Accounts\Entities\Role\Role;
use Modules\Accounts\Entities\Role\RoleScope;
use Modules\Accounts\Entities\Scope\Scope;
use Modules\Accounts\Tests\PassportTestCase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RolesScopesTest extends PassportTestCase
{
    /**
     * a_user_can_update_scopes_of_company_role
     *
     * @test
     */
    public function a_user_can_update_scopes_of_company_role()
    {
        $role = Role::all()->first();
        $newCompany = Company::factory ()->create ();
        $requestDataAddRole = $this->requestDataAddRole($role);
        $response = $this->patch ('/api/company_roles/'. $newCompany->id, $requestDataAddRole);

        $requestDataAddScopes = $this->requestData($newCompany->id);
        $response = $this->patch ('/api/role_scopes/'. $role->id, $requestDataAddScopes);

        $response->assertStatus (200);
        $this->assertDatabaseHas ('role_scopes', [
            'role_id' => $role->id,
            'company_id' => $newCompany->id,
            'scope_id' => $requestDataAddScopes['scopes'][0]['scope_id']
        ]);

        $this->assertDatabaseHas ('role_scopes', [
            'role_id' => $role->id,
            'company_id' => $newCompany->id,
            'scope_id' => $requestDataAddScopes['scopes'][1]['scope_id']
        ]);

        $this->assertDatabaseHas ('role_scopes', [
            'role_id' => $role->id,
            'company_id' => $newCompany->id,
            'scope_id' => $requestDataAddScopes['scopes'][2]['scope_id']
        ]);
    }

    /**
     * a_user_can_update_scopes_of_role
     *
     * @test
     */
    public function a_user_can_destroy_scopes_of_role()
    {
        $role = Role::all()->first();
        $newCompany = Company::factory ()->create ();
        $requestDataAddRole = $this->requestDataAddRole($role);
        //CREATE
        $response = $this->patch ('/api/company_roles/'. $newCompany->id, $requestDataAddRole);

        $requestDataAddScopes = $this->requestData($newCompany->id);
        //CREATE
        $response = $this->patch ('/api/role_scopes/'. $role->id, $requestDataAddScopes);

        //DELETE
        $response = $this->post ('/api/role_scopes/delete/'. $role->id, $requestDataAddScopes);

        $response->assertStatus (200);
        $this->assertDatabaseMissing ('role_scopes', [
            'role_id' => $role->id,
            'company_id' => $newCompany->id,
            'scope_id' => $requestDataAddScopes['scopes'][0]['scope_id']
        ]);

        $this->assertDatabaseMissing ('role_scopes', [
            'role_id' => $role->id,
            'company_id' => $newCompany->id,
            'scope_id' => $requestDataAddScopes['scopes'][1]['scope_id']
        ]);

        $this->assertDatabaseMissing ('role_scopes', [
            'role_id' => $role->id,
            'company_id' => $newCompany->id,
            'scope_id' => $requestDataAddScopes['scopes'][2]['scope_id']
        ]);
    }

    private function requestData($company_id) {
        $scopes = Scope::all ()->toArray ();
        return [
            'company_id' => $company_id,
            'scopes' => [
                [
                    'scope_id' => $scopes[0]['id']
                ],
                [
                    'scope_id' => $scopes[1]['id']
                ],
                [
                    'scope_id' => $scopes[3]['id']
                ],
            ]
        ];
    }

    private function requestDataAddRole($role)
    {
        return [
            'roles' => [
                [
                    'role_id' => $role->id
                ],
            ]
        ];
    }
}
