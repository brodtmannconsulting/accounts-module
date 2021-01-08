<?php

namespace Modules\Accounts\Tests\Feature;
use Illuminate\Http\Response;
use Modules\Accounts\Entities\Company\Company;
use Modules\Accounts\Tests\CompanyTestCase;

class CompanyPolicyTest extends CompanyTestCase
{
    /**
     *
     * @test
     */
    public function an_admin_user_can_not_see_list_of_companies()
    {
        $user = $this->createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        $response = $this->get('/api/companies');
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */
    public function a_user_with_role_user_can_not_see_list_of_companies()
    {
        $user = $this->createUserWithRole('maximuss','123456789',$this->company->id,'user');
        $response = $this->get('/api/companies');
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */
    public function an_admin_user_can_not_store_new_companies()
    {
        $user = $this->createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        $response = $this->post('/api/companies',$this->requestData ());
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */
    public function a_user_with_role_user_can_not_store_new_companies()
    {
        $user = $this->createUserWithRole('maximuss','123456789',$this->company->id,'user');
        $response = $this->post('/api/companies',$this->requestData ());
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */
    public function an_admin_user_can_see_his_company()
    {
        $this->withoutExceptionHandling ();
        $user = $this->createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        $response = $this->get('/api/companies/'.$this->company->id);
        $response->assertStatus(Response::HTTP_OK);
    }

    /**
     *
     * @test
     */
    public function an_admin_user_can_not_see_another_company()
    {
        $new_company = Company::factory()->create();
        $user = $this->createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        $response = $this->get('/api/companies/'.$new_company->id);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */
    public function a_user_with_role_user_can_not_see_specific_company()
    {
        $new_company = Company::factory()->create();
        $user = $this->createUserWithRole('maximuss','123456789',$this->company->id,'user');
        $response = $this->get('/api/companies/'.$this->company->id);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $response = $this->get('/api/companies/'.$new_company->id);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */
    public function an_admin_user_can_update_own_company()
    {
        $user = $this->createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        $response = $this->patch('/api/companies/'.$this->company->id, ['name' => 'Updated']);
        $response->assertStatus(Response::HTTP_OK);
    }

    /**
     *
     * @test
     */
    public function an_admin_user_can_not_update_another_company()
    {
        $new_company = Company::factory()->create();
        $user = $this->createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        $response = $this->patch('/api/companies/'.$new_company->id, ['name' => 'Updated']);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }


    /**
     *
     * @test
     */
    public function a_user_with_role_user_can_not_update_any_companies()
    {
        $new_company = Company::factory()->create();
        $user = $this->createUserWithRole('maximuss','123456789',$this->company->id,'user');
        $response = $this->patch('/api/companies/'.$this->company->id, ['name' => 'Updated']);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $response = $this->patch('/api/companies/'.$new_company->id, ['name' => 'Updated']);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */
    public function an_admin_user_can_not_delete_any_companies()
    {
        $new_company = Company::factory()->create();
        $user = $this->createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        $response = $this->delete('/api/companies/'.$this->company->id);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $response = $this->delete('/api/companies/'.$new_company->id);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */
    public function a_user_with_role_user_can_not_delete_any_companies()
    {
        $new_company = Company::factory()->create();
        $user = $this->createUserWithRole('maximuss','123456789',$this->company->id,'user');
        $response = $this->delete('/api/companies/'.$this->company->id);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $response = $this->delete('/api/companies/'.$new_company->id);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

}
