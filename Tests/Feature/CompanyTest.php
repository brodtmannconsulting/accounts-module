<?php

namespace Modules\Accounts\Tests\Feature;

use Illuminate\Http\Response;
use Modules\Accounts\Entities\Company\Company;
use Modules\Accounts\Entities\Company\CompanyRole;
use Modules\Accounts\Entities\Role\RoleScope;
use Modules\Accounts\Entities\User\User;
use Modules\Accounts\Tests\CompanyTestCase;

class CompanyTest extends CompanyTestCase
{
    /**
     * A basic feature test example.
     *
     * @test
     */
    public function a_user_can_get_list_of_companies()
    {
        $this->withoutExceptionHandling ();
        $response = $this->get ('/api/companies');
        $response->assertStatus (200)->assertJson ([
            'data' => [$this->companyData ()]
        ]);
    }

    private function companyData($company = null)
    {
        if (is_null ($company)) $company = $this->company;
        return [
            'data' => [
                'company_id' => $company->id,
                'name' => $company->name,
                'description' => $company->description,
                'company_website' => $company->company_website,
                // 'roles' => RoleResource::collection ($this->company->roles),
                'created_at' => $company->created_at->format ('d-m-Y H:i'),
            ],
            'links' => [
                'self' => $company->path ()
            ]
        ];
    }

    /**
     * A basic feature test example.
     *
     * @test
     */
    public function a_user_can_store_new_company()
    {
        $this->withoutExceptionHandling ();
        $company_name = 'NEW COMPANY';
        $response = $this->post ('/api/companies', array_merge ($this->requestData (), ['name' => $company_name]));
        $response->assertStatus (Response::HTTP_CREATED);
        $this->assertDatabaseHas ('companies', ['id' => $response->getOriginalContent ()->id, 'name' => $company_name]);
        $company = Company::findOrFail ($response->getOriginalContent ()->id);
        $response->assertJson ($this->companyData ($company));
    }

    /**
     * A basic feature test example.
     *
     * @test
     */
    public function a_user_can_see_a_specific_company()
    {
        $this->withoutExceptionHandling ();
        $response = $this->get ('/api/companies/' . $this->company->id);
        $response->assertStatus (Response::HTTP_OK)->assertJson ($this->companyData ());
    }

    /**
     * A basic feature test example.
     *
     * @test
     */
    public function a_user_can_update_a_specific_company()
    {
        $company_name = 'Updated Name';
        $this->withoutExceptionHandling ();
        $response = $this->post ('/api/companies/' . $this->company->id, ['name' => $company_name]);
        $this->company->name = $company_name;
        $response->assertStatus (Response::HTTP_OK)->assertJson ($this->companyData ($this->company));
    }

    /**
     * A basic feature test example.
     *
     * @test
     */
    public function a_user_can_delete_a_specific_company()
    {
        $this->withoutExceptionHandling ();
        $response = $this->delete ('/api/companies/' . $this->company->id);
        $response->assertStatus (Response::HTTP_OK)->assertJson ($this->companyData ());
        $this->assertCount (0, Company::where ('id', $this->company->id)->get ());
        $this->assertCount (0, User::where ('company_id', $this->company->id)->get ());
        $this->assertCount (0, CompanyRole::where ('company_id', $this->company->id)->get ());
        $this->assertCount (0, RoleScope::where ('company_id', $this->company->id)->get ());
    }
}
