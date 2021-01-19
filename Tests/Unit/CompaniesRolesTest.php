<?php

namespace Modules\Accounts\Tests\Unit;

use Modules\Accounts\Entities\Role\Role;
use Modules\Accounts\Tests\PassportTestCase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CompaniesRolesTest extends PassportTestCase
{
    /**
     * A basic unit a_user_can_get_role_of_his_company example.
     *
     * @test
     */
    public function a_user_can_get_role_of_his_company()
    {
        $this->withoutExceptionHandling ();
        $role = Role::all()->first();
        $company = auth ('api')->user()->user->company;
        $response = $this->post ('api/company_roles/role/'. $company->id, [
            'roles' => [
                [
                    'role_id' => $role->id
                ]
            ]
        ]);
        $response->assertStatus (200);
    }
}
