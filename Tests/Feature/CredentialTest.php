<?php

namespace Modules\Accounts\Tests\Feature;

use Illuminate\Http\Response;
use Laravel\Passport\Passport;
use Modules\Accounts\Entities\Credential\Credential;
use Modules\Accounts\Tests\CredentialTestCase;

class CredentialTest extends CredentialTestCase
{
    /**
     *
     * @test void
     */
    public function a_user_can_get_list_of_all_credentials()
    {
        $response = $this->get ('/api/credentials');
        $response->assertStatus (Response::HTTP_OK)->assertJson ([
            'data' => [$this->credentialData ()]
        ]);
    }

    /**
     *
     * @test void
     */
    public function a_user_can_get_list_of_company_credentials()
    {
        $this->withoutExceptionHandling ();
        Passport::actingAs ($this->user->credentials ()->first (), $this->user->getScopes ());
        $response = $this->get ('/api/company_credentials/' . $this->company->id);
        $response->assertStatus (Response::HTTP_OK)->assertJson ([
            'data' => [$this->credentialData ()]
        ]);
    }

    /**
     *
     * @test void
     */
    public function a_user_can_store_new_credentials()
    {
        $this->withoutExceptionHandling ();
        $username = 'MaxPrimax';
        Passport::actingAs ($this->user->credentials ()->first (), $this->user->getScopes ());
        $response = $this->post ('/api/credentials', array_merge ($this->requestData (), ['username' => $username]));
        $response->assertStatus (Response::HTTP_CREATED);
        $this->assertDatabaseHas ('credentials', [
            'user_id' => $this->user->id,
            'id' => $response->getOriginalContent ()->id,
            'username' => Credential::getHashedUsername ($username),
        ]);
        $this->assertEquals (decrypt (Credential::findOrFail ($response->getOriginalContent ()->id)->AES_256_username), $username);

        $this->assertDatabaseMissing ('credentials', [
            'password' => '123456789',
        ]);

        $response->assertJson ([
            'data' => [
                'username' => $username
            ]
        ]);
    }

    /**
     *
     * @test void
     */
    public function a_user_can_get_a_specific_credential()
    {
        Passport::actingAs ($this->user->credentials ()->first (), $this->user->getScopes ());
        $response = $this->get ('/api/credentials/' . $this->user->credentials ()->first ()->id);
        $response->assertStatus (Response::HTTP_OK)->assertJson ($this->credentialData ());
    }

    /**
     *
     * @test void
     */
    public function a_user_can_update_a_specific_credential()
    {
        $updated_username = 'Updated Username';
        Passport::actingAs ($this->user->credentials ()->first (), $this->user->getScopes ());
        $response = $this->patch ('/api/credentials/' . $this->user->credentials ()->first ()->id, ['username' => $updated_username]);
        $response->assertStatus (Response::HTTP_OK);
        $this->assertDatabaseHas ('credentials', ['id' => $this->user->credentials ()->first ()->id, 'username' => Credential::getHashedUsername ($updated_username)]);
        $response->assertJson ([
            'data' => [
                'credential_id' => $this->user->credentials ()->first ()->id,
                'username' => $updated_username
            ]
        ]);
    }

    /**
     *
     * @test void
     */
    public function a_user_can_delete_a_specific_credential()
    {
        $this->withoutExceptionHandling ();
        $credential = Credential::factory()->create (self::getCredentialsData ($this->user, 'Maxonich', '123456789'));
        Passport::actingAs ($this->user->credentials ()->first (), $this->user->getScopes ());
        $response = $this->delete ('/api/credentials/' . $credential->id);
        $response->assertStatus (Response::HTTP_OK);
        $this->assertEquals (Credential::find ($credential->id), null);
        $response->assertJson ([
            'data' => []
        ]);
    }
}
