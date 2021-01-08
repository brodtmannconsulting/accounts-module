<?php

namespace Modules\Accounts\Tests\Feature;

use Illuminate\Http\Response;
use Laravel\Passport\Passport;
use Modules\Accounts\Entities\Company\Company;
use Modules\Accounts\Entities\Role\Role;
use Modules\Accounts\Tests\RoleTestCase;

class RolesTest extends RoleTestCase
{

    /**
     *
     * @test
     */
    public function a_user_can_get_list_of_roles()
    {
        $this->withoutExceptionHandling ();
        Passport::actingAs ($this->user->credentials()->first(),$this->user->getScopes ());
        $response = $this->get('api/roles');
        $response->assertStatus (Response::HTTP_OK)->assertJson ([
            'data' => [ $this->RoleData() ]
        ]);
    }

    /**
     *
     * @test
     */
    public function a_user_can_get_list_of_roles_of_a_specific_company()
    {
        $this->withoutExceptionHandling ();
        Passport::actingAs ($this->user->credentials()->first(),$this->user->getScopes ());
        $response = $this->get('api/company_roles/'.$this->company->id);
        $response->assertStatus (Response::HTTP_OK)->assertJson ([
            'data' => [ $this->RoleData() ]
        ]);
    }

    /**
     *
     * @test
     */
    public function a_user_can_store_new_role()
    {
        $role_name = 'NEW ROLE';
        $new_company = Company::factory()->times(5)->create ();

        Passport::actingAs ($this->user->credentials()->first(),$this->user->getScopes ());
        $response = $this->post('api/roles/',array_merge ($this->requestData(),['name' => $role_name]));

        $response->assertStatus (Response::HTTP_CREATED);
        $this->assertDatabaseHas ('roles',['name' => $role_name]);
        $role = Role::where('name',$role_name)->firstOrFail();

        $new_company->each (function ($company) use ($role){
            $this->assertDatabaseHas ('company_roles',['company_id' => $company->id, 'role_id' => $role->id]);
        });
        foreach ($this->companies_ids as $company_id){
            $this->assertDatabaseHas ('role_scopes',[
                'scope_id' => $this->user_account_list_all->id,
                'role_id' => $role->id,
                'company_id' => $company_id
            ]);
        }

        $response->assertJson ($this->RoleData($role));
    }


    /**
     *
     * @test
     */
    public function a_user_can_update_role()
    {
        $role_name = 'Updated NEW ROLE NAME';

        $response = $this->patch('api/roles/'.$this->user->roles()->firstOrFail ()->id, ['name' => $role_name]);

        $response->assertStatus (Response::HTTP_OK);
        $this->assertDatabaseHas ('roles',['name' => $role_name]);

        $role = Role::where('name',$role_name)->firstOrFail();
        $response->assertJson ($this->RoleData($role));
    }


    /**
     *
     * @test
     */
    public function a_role_can_be_shown()
    {
        Passport::actingAs ($this->user->credentials()->first(),$this->user->getScopes ());
        $response = $this->get('api/roles/'.$this->user->roles()->firstOrFail ()->id);
        $response->assertStatus (Response::HTTP_OK)->assertJson ($this->RoleData());
    }

    /**
     *
     * @test
     */
    public function a_role_can_be_deleted()
    {
        $this->withoutExceptionHandling ();
        Passport::actingAs ($this->user->credentials()->first(),$this->user->getScopes ());
        $role = $this->user->roles()->firstOrFail ();
        $response = $this->delete('api/roles/'.$role->id);
        $response->assertStatus (Response::HTTP_OK)->assertJson ($this->RoleData($role));
        $this->assertDatabaseMissing ('roles', $response->getOriginalContent ()->toArray());
        $this->assertDatabaseMissing('user_roles', ['role_id' => $role->id]);
        $this->assertDatabaseMissing('role_scopes', ['role_id' => $role->id]);
    }

    //TODO::TEST FOR VALIDATION


    private function RoleData($role = null)
    {
        if (is_null($role)) $role = $this->user->roles ()->first ();
        return [
            'data' => [
                'role_id' => $role->id,
                'name' => $role->name,
                'description' => $role->description,
                'is_custom' => $role->is_custom,
                'role_created_at_for_humans' => $role->created_at->format('d-m-Y H:i'),
                'role_created_at' => $role->created_at->format('d-m-Y H:i'),
                // 'scopes' => ScopeResource::collection ($role->scopes)
            ],
            'links' => [
                'self' => $role->path()
            ]
        ];
    }
}
