<?php

namespace Modules\Accounts\Tests\Feature;

use Illuminate\Http\Response;
use Laravel\Passport\Passport;
use Modules\Accounts\Entities\Company\Company;
use Modules\Accounts\Entities\Role\Role;
use Modules\Accounts\Tests\RoleTestCase;

class RolePolicyTest extends RoleTestCase
{
    /**
     *
     * @test
     */

    public function an_admin_user_can_not_see_all_roles()
    {
        $user = $this->createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        $response = $this->get('api/roles');
        $response->assertStatus (Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */

    public function a_user_with_role_user_can_not_see_all_roles()
    {
        $user = $this->createUserWithRole('maximuss','123456789',$this->company->id,'user');
        $response = $this->get('api/roles');
        $response->assertStatus (Response::HTTP_FORBIDDEN);
    }


    /**
     *
     * @test
     */

    public function an_admin_user_can_see_roles_of_own_company()
    {
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        $response = $this->get('api/company_roles/'.$this->company->id);
        $response->assertStatus (Response::HTTP_OK);
    }

    /**
     *
     * @test
     */

    public function an_admin_user_can_not_see_roles_of_other_companies()
    {
        $new_company = Company::factory()->create ();
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        $response = $this->get('api/company_roles/'.$new_company->id);
        $response->assertStatus (Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */

    public function a_user_with_role_user_can_not_see_roles_of_companies()
    {
        $new_company = Company::factory()->create ();
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'user');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->get('api/company_roles/'.$new_company->id);
        $response->assertStatus (Response::HTTP_FORBIDDEN);
        $response = $this->get('api/company_roles/'.$this->company->id);
        $response->assertStatus (Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */

    public function an_admin_user_can_store_roles_for_own_company()
    {
        $this->withoutExceptionHandling ();
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->post('api/roles/',[
            'name' => 'New Role',
            'description' =>  'Role Description',
            'is_custom' => 1,
            'scopes' => [
                [
                    'scope_id' => $user->roles ()->first ()->scopes ()->first ()->id,
                    'companies_ids' => [
                        $this->company->id
                    ],
                ],
                [
                    'scope_id' => $user->roles ()->first ()->scopes ()->where('name','system_roles_update_company')->first()->id ,
                    'companies_ids' => [
                        $this->company->id
                    ],
                ],
            ],
        ]);
        $response->assertStatus (Response::HTTP_CREATED);
    }


    /**
     *
     * @test
     */

    public function an_admin_user_can_not_store_roles_which_he_does_not_own()
    {
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->post('api/roles/',[
            'name' => 'New Role',
            'description' =>  'Role Description',
            'is_custom' => 1,
            'scopes' => [
                [
                    'scope_id' => $this->user_account_list_all->id,
                    'companies_ids' => [
                        $this->company->id
                    ],
                ],
                [
                    'scope_id' => $this->user_account_add_all->id,
                    'companies_ids' => [
                        $this->company->id
                    ],
                ],
            ],
        ]);
        $response->assertStatus (Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */

    public function an_admin_user_can_not_store_not_custom_roles()
    {
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->post('api/roles/',[
            'name' => 'New Role',
            'description' =>  'Role Description',
            'is_custom' =>  '0',
            'scopes' => [
                [
                    'scope_id' => $user->roles ()->first ()->scopes ()->first ()->id,
                    'companies_ids' => [
                        $this->company->id
                    ],
                ],
            ],
        ], [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus (Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertDatabaseMissing ('roles',[
            'name' => 'New Role',
            'is_custom' => 0,
        ]);
    }

    /**
     *
     * @test
     */

    public function an_admin_user_can_not_store_roles_for_another_company()
    {
        $new_companies = Company::factory()->times(5)->create ();
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->post('api/roles/',$this->requestData());
        $response->assertStatus (Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */

    public function a_user_with_role_user_can_not_store_any_roles()
    {

        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'user');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->post('api/roles/',$this->requestData());
        $response->assertStatus (Response::HTTP_FORBIDDEN);
        $new_companies = Company::factory()->times(5)->create ();
        $response = $this->post('api/roles/',$this->requestData());
        $response->assertStatus (Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */

    public function an_admin_user_can_see_role_of_his_company()
    {

        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->get('api/roles/'.$user->roles ()->firstOrFail ()->id);
        $response->assertStatus (Response::HTTP_OK);
    }

    /**
     *
     * @test
     */

    public function an_admin_user_can_not_see_role_of_another_company()
    {
        $new_company = Company::factory()->create ();
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        $user_from_another_company = self::createUserWithRole('another_company_user','123456789',$new_company->id,'new_role');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->get('api/roles/'.$user_from_another_company->roles ()->firstOrFail ()->id);
        $response->assertStatus (Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */

    public function a_user_with_role_user_can_not_see_any_roles()
    {
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'user');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->get('api/roles/'.$user->roles ()->firstOrFail ()->id);
        $response->assertStatus (Response::HTTP_FORBIDDEN);
    }


    /**
     *
     * @test
     */

    public function an_admin_user_can_update_custom_roles_for_own_company()
    {
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());

        $response = $this->post('api/roles/',[
            'name' => 'New Role',
            'description' =>  'Role Description',
            'is_custom' => 1,
            'scopes' => [
                [
                    'scope_id' => $user->roles ()->first ()->scopes ()->first ()->id,
                    'companies_ids' => [
                        $this->company->id
                    ],
                ],
            ],
        ]);

        $response = $this->patch('api/roles/'.$response->getOriginalContent ()->id, ['name' => 'UPDATED ROLE NAME']);
        $response->assertStatus (Response::HTTP_OK);
    }

    /**
     *
     * @test
     */

    public function an_admin_user_can_not_update_not_custom_roles_for_own_company()
    {
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->patch('api/roles/'.$user->roles ()->firstOrFail()->id, ['name' => 'NEW ROLE NAME']);
        $response->assertStatus (Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */

    public function an_admin_user_can_not_update_roles_of_other_companies()
    {
        $new_company = Company::factory()->create ();
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        $user_from_another_company = self::createUserWithRole('another_company_user','123456789',$new_company->id,'new_role',true);
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->patch('api/roles/'.$user_from_another_company->roles ()->firstOrFail()->id, ['name' => 'NEW ROLE NAME']);
        $response->assertStatus (Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */

    public function an_admin_user_can_not_update_super_user_role()
    {
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $role = Role::where('name', 'super_user')->first();
        $response = $this->patch('api/roles/'.$role->id, ['name' => 'NEW NAME']);
        $response->assertStatus (Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */

    public function a_user_with_role_user_can_not_update_any_roles()
    {
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'user');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $role = Role::where('name', 'super_user')->first();
        $response = $this->patch('api/roles/'.$role->id, ['name' => 'NEW NAME']);
        $response->assertStatus (Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */

    public function an_admin_user_can_delete_custom_roles_of_his_company()
    {
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        $role = self::createRole('new_role', $this->company->id,$user->id,true);
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->delete('api/roles/'.$role->id);
        $response->assertStatus (Response::HTTP_OK);
    }

    /**
     *
     * @test
     */

    public function an_admin_user_can_not_delete_not_custom_roles_of_his_company()
    {
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->delete('api/roles/'.$user->roles ()->firstOrFail ()->id);
        $response->assertStatus (Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */

    public function an_admin_user_can_not_delete_custom_roles_of_another_company()
    {
        $new_company = Company::factory()->create ();
        $user = self::createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        $user_from_another_company = self::createUserWithRole('another','123456789',$new_company->id,'new_role',true);
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->delete('api/roles/'.$user_from_another_company->roles ()->where ('is_custom',1)->firstOrFail()->id);
        $response->assertStatus (Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */

    public function a_user_with_role_user_can_not_delete_any_roles()
    {
        $user = $this->createUserWithRole('maximuss','123456789',$this->company->id,'user');
        $role = Role::where('name', 'super_user')->first();
        $response = $this->delete('api/roles/'.$role->id);
        $response->assertStatus (Response::HTTP_FORBIDDEN);
    }
}
