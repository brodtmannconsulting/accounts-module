<?php

namespace Modules\Accounts\Tests\Feature;
use Illuminate\Http\Response;
use Laravel\Passport\Passport;
use Modules\Accounts\Entities\Company\Company;
use Modules\Accounts\Entities\Credential\Credential;
use Modules\Accounts\Tests\CredentialTestCase;

class CredentialPolicyTest extends CredentialTestCase
{
    /**
     *
     * @test
     */
    public function an_admin_user_can_not_get_all_credentials()
    {
        $user = $this->createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        $response = $this->get('/api/credentials');
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */
    public function a_user_with_role_user_can_not_get_all_credentials()
    {
        $user = $this->createUserWithRole('maximuss','123456789',$this->company->id,'user');
        $response = $this->get('/api/credentials');
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */
    public function an_admin_user_can_get_company_users_credentials()
    {
        $this->withoutExceptionHandling ();
        $user = $this->createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        $response = $this->get('/api/company_credentials/'.$this->company->id);
        $response->assertStatus(Response::HTTP_OK);
    }

    /**
     *
     * @test
     */
    public function an_admin_user_can_not_get_credentials_of_another_company()
    {
        $new_company = Company::factory()->create();
        $user = $this->createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        $another_user = $this->createUserWithRole('another_user','123456789',$new_company->id,'user');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->get('/api/company_credentials/'.$new_company->id);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */
    public function a_user_with_role_user_can_not_get_credentials_of_companies()
    {
        $new_company = Company::factory()->create();
        $user = $this->createUserWithRole('maximuss','123456789',$this->company->id,'user');
        $another_user = $this->createUserWithRole('another_user','123456789',$new_company->id,'admin');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->get('/api/company_credentials/'.$this->company->id);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $response = $this->get('/api/company_credentials/'.$new_company->id);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */
    public function an_admin_user_can_store_credentials_for_company_users()
    {
        $this->withoutExceptionHandling ();
        $user = $this->createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->post('/api/credentials/',array_merge ($this->requestData (),['user_id' => $user->id]));
        $response->assertStatus(Response::HTTP_CREATED);
    }

    /**
     *
     * @test
     */
    public function an_admin_user_can_not_store_credentials_for_users_from_another_company()
    {
        $new_company = Company::factory()->create();
        $user = $this->createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        $another_user = $this->createUserWithRole('another_user','123456789',$new_company->id,'user');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->post('/api/credentials/',array_merge ($this->requestData (),['user_id' => $another_user->id]));
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */
    public function a_user_with_role_user_can_store_credentials_for_himself()
    {
        $user = $this->createUserWithRole('maximuss','123456789',$this->company->id,'user');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->post('/api/credentials/',array_merge ($this->requestData (),['user_id' => $user->id]));
        $response->assertStatus(Response::HTTP_CREATED);
    }

    /**
     *
     * @test
     */
    public function a_user_with_role_user_can_not_store_credentials_for_other_users()
    {
        $new_company = Company::factory()->create();
        $user = $this->createUserWithRole('maximuss','123456789',$this->company->id,'user');
        $another_user = $this->createUserWithRole('another_user','123456789',$new_company->id,'admin');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->post('/api/credentials/',array_merge ($this->requestData (),['user_id' => $this->user->id]));
        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $response = $this->post('/api/credentials/',array_merge ($this->requestData (),['user_id' => $another_user->id]));
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */
    public function an_admin_user_can_get_a_specific_credential_of_company_user()
    {
        $this->withoutExceptionHandling ();
        $user = $this->createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        $another_user = $this->createUserWithRole('another_user','123456789',$this->company->id,'user');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->get('/api/credentials/'.$another_user->credentials ()->first ()->id);
        $response->assertStatus(Response::HTTP_OK);
    }

    /**
     *
     * @test
     */
    public function an_admin_user_can_not_get_a_specific_credential_of_company_user()
    {
        $new_company = Company::factory()->create();
        $user = $this->createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        $another_user = $this->createUserWithRole('another_user','123456789',$new_company->id,'user');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->get('/api/credentials/'.$another_user->credentials ()->first ()->id);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */
    public function a_user_with_role_user_can_get_a_specific_credential_only_of_own_user()
    {
        $user = $this->createUserWithRole('maximuss','123456789',$this->company->id,'user');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->get('/api/credentials/'.$user->credentials ()->first ()->id);
        $response->assertStatus(Response::HTTP_OK);
    }

    /**
     *
     * @test
     */
    public function a_user_with_role_user_can_not_get_a_specific_credential_of_other_users()
    {
        $new_company = Company::factory()->create();
        $user = $this->createUserWithRole('maximuss','123456789',$this->company->id,'user');
        $another_user = $this->createUserWithRole('another_user','123456789',$new_company->id,'admin');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->get('/api/credentials/'.$this->user->credentials ()->first()->id);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $response = $this->get('/api/credentials/'.$another_user->credentials ()->first ()->id);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */
    public function an_admin_user_can_update_a_specific_credential_of_company_users()
    {
        $updated_username = 'NEW';
        $user = $this->createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        $another_user = $this->createUserWithRole('another_user','123456789',$this->company->id,'user');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->patch('/api/credentials/'.$user->credentials()->first()->id,['username' => 'UPDATED']);
        $response->assertStatus(Response::HTTP_OK);
        $response = $this->patch('/api/credentials/'.$another_user->credentials()->first()->id,['username' => $updated_username]);
        $response->assertStatus(Response::HTTP_OK);
    }

    /**
     *
     * @test
     */
    public function an_admin_user_can_not_update_a_specific_credential_of_users_from_another_company()
    {
        $updated_username = 'NEW';
        $new_company = Company::factory()->create();
        $user = $this->createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        $another_user = $this->createUserWithRole('another_user','123456789',$new_company->id,'user');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->patch('/api/credentials/'.$another_user->credentials()->first()->id,['username' => $updated_username]);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */
    public function a_user_with_role_user_can_update_a_specific_credential_of_own_user()
    {
        $updated_username = 'NEW';
        $user = $this->createUserWithRole('maximuss','123456789',$this->company->id,'user');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->patch('/api/credentials/'.$user->credentials()->first()->id,['username' => $updated_username]);
        $response->assertStatus(Response::HTTP_OK);
    }

    /**
     *
     * @test
     */
    public function a_user_with_role_user_can_not_update_a_specific_credential_of_other_users()
    {
        $updated_username = 'NEW';
        $new_company = Company::factory()->create();
        $user = $this->createUserWithRole('maximuss','123456789',$this->company->id,'user');
        $another_user = $this->createUserWithRole('another_user','123456789',$new_company->id,'admin');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->patch('/api/credentials/'.$this->user->credentials()->first()->id,['username' => $updated_username]);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $response = $this->patch('/api/credentials/'.$another_user->credentials()->first()->id,['username' => 'sdd']);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }


    /**
     *
     * @test
     */
    public function an_admin_user_can_delete_a_specific_credential_of_company_users()
    {
        $user = $this->createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        $another_user = $this->createUserWithRole('another_user','123456789',$this->company->id,'user');
        $credential = Credential::factory()->create (self::getCredentialsData($another_user,'One_more_credential','123456789'));
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->delete('/api/credentials/'.$credential->id);
        $response->assertStatus(Response::HTTP_OK);
    }

    /**
     *
     * @test
     */
    public function an_admin_user_can_not_delete_a_last_credential_of_company_user()
    {
        $user = $this->createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        $another_user = $this->createUserWithRole('another_user','123456789',$this->company->id,'user');
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->delete('/api/credentials/'.$another_user->credentials ()->first()->id);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */
    public function an_admin_user_can_not_delete_a_specific_credential_of_users_from_another_company()
    {
        $new_company = Company::factory()->create();
        $user = $this->createUserWithRole('maximuss','123456789',$this->company->id,'admin');
        $another_user = $this->createUserWithRole('another_user','123456789',$new_company->id,'user');
        $credential = Credential::factory()->create (self::getCredentialsData($another_user,'One_more_credential','123456789'));
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->delete('/api/credentials/'.$credential->id);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /**
     *
     * @test
     */
    public function a_user_with_role_user_can_delete_a_specific_credential_of_own_user()
    {
        $user = $this->createUserWithRole('maximuss','123456789',$this->company->id,'user');
        $credential = Credential::factory()->create (self::getCredentialsData($user,'One_more_credential','123456789'));
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->delete('/api/credentials/'.$credential->id);
        $response->assertStatus(Response::HTTP_OK);
    }

    /**
     *
     * @test
     */
    public function a_user_with_role_user_can_not_delete_a_specific_credentials_of_other_users()
    {
        $new_company = Company::factory()->create();
        $user = $this->createUserWithRole('maximuss','123456789',$this->company->id,'user');
        $user_from_another_company = $this->createUserWithRole('another_user','123456789',$new_company->id,'admin');
        $credential_of_admin = Credential::factory()->create (self::getCredentialsData($user_from_another_company,'One_more_credential','123456789'));
        $credential_of_super_user = Credential::factory()->create (self::getCredentialsData($this->user,'usernamee','123456789'));
        Passport::actingAs($user->credentials()->first(),$user->getScopes());
        $response = $this->delete('/api/credentials/'.$credential_of_admin->id);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $response = $this->delete('/api/credentials/'.$credential_of_super_user->id);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }
}
