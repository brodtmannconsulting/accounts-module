<?php

namespace Modules\Accounts\Tests\Feature;

use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Laravel\Passport\Passport;
use Modules\Accounts\Entities\Company\Company;
use Modules\Accounts\Entities\Credential\Credential;
use Modules\Accounts\Entities\Role\UserRole;
use Modules\Accounts\Entities\User\User;
use Modules\Accounts\Entities\User\UserRsaKey;
use Modules\Accounts\Tests\PassportTestCase;

class UsersTest extends PassportTestCase
{

    /**
     * @test
     */
    public function a_user_with_access_to_all_users_can_get_all_users()
    {
        Passport::actingAs($this->user->credentials()->first(),$this->user->getScopes());
        $response = $this->get ('api/users');
        $response->assertJson([
            'data' => [ $this->userData () ]
        ]);
    }

//    /**
//     * @test
//     */
//    public function a_unauthenticated_user_can_not_get_users()
//    {
//        $response = $this->get ('api/users');
//        $response->assertStatus (Response::HTTP_UNAUTHORIZED);
//    }

    /**
     * @test
     */
    public function a_user_without_access_to_all_users_can_not_get_users()
    {
        Passport::actingAs($this->user->credentials()->first());
        $response = $this->get ('api/users');
        $response->assertStatus (Response::HTTP_FORBIDDEN);
    }

    /**
     * @test
     */
    public function a_user_can_store_new_users()
    {
        $this->withoutExceptionHandling ();
        Passport::actingAs($this->user->credentials()->first(),$this->user->getScopes());
        $this->assertCount (1 , User::all ());

        $response = $this->post('api/users',array_merge ($this->requestData(),['first_name' => 'Maximus', 'company_id' => $this->company->id]));
        $response->assertStatus (Response::HTTP_CREATED)->assertJson([
            'data' => [
                'first_name' => 'Maximus'
            ]
        ]);

        $this->assertCount (2 , User::all ());

        $this->assertDatabaseHas ('credentials',[
            'username' => Credential::getHashedUsername ('test_user_name'),
        ]);

        $this->assertDatabaseHas ('credentials',[
            'username' => Credential::getHashedUsername ('test_username@test.com'),
        ]);
    }

    /** @test */
    public function fields_are_required(){
        Passport::actingAs($this->user->credentials()->first(),$this->user->getScopes());
        collect (['first_name','last_name','company_id','credentials'])
            ->each(function($filed){
                $response = $this->post ('api/users',
                    array_merge ($this->requestData (), [$filed => '']), ['Accept' => 'application/json']);
                $this->assertCount (1 , User::all ());
                $response->assertStatus (Response::HTTP_UNPROCESSABLE_ENTITY);
            });
    }

    /** @test */
    public function valid_until_is_properly_stored(){
        Passport::actingAs($this->user->credentials()->first(),$this->user->getScopes());
        $response = $this->post('api/users',$this->requestData());
        $user = User::findOrFail($response->getOriginalContent ()->id);

        $this->assertCount (2 , User::all ());
        $this->assertInstanceOf (Carbon::class,$user->credentials->first()->valid_until);
    }

    /** @test */
    public function a_specific_user_can_be_showed(){
        Passport::actingAs($this->user->credentials()->first(),$this->user->getScopes());
        $response = $this->get('api/users/'.$this->user->id);
        $response->assertStatus (Response::HTTP_OK)->assertJson($this->userData ());
    }

    /** @test */
    public function a_user_can_be_updated(){
        Passport::actingAs($this->user->credentials()->first(),$this->user->getScopes());
        $response = $this->post('api/users/'.$this->user->id,['first_name' => 'new_first_name']);
        $response->assertStatus (Response::HTTP_OK);
        $user = User::findOrFail($response->getOriginalContent ()->id);
        $this->assertEquals ('new_first_name',decrypt ($user->first_name));
    }

    /** @test */
    public function an_allow_log_in_of_user_can_be_updated(){
        Passport::actingAs($this->user->credentials()->first(),$this->user->getScopes());
        $user_of_his_company = self::createUserWithCredential($this->company->id,'Another User','123456789');
        $response = $this->patch('api/update_allow_log_in/'.$user_of_his_company->id,['allow_log_in' => '0']);
        $response->assertStatus (Response::HTTP_OK);
        $user = User::findOrFail($response->getOriginalContent ()->id);
        $this->assertEquals ('0',$user->allow_log_in);
    }

    /** @test */
    public function a_user_can_be_deleted(){
        Passport::actingAs($this->user->credentials()->first(),$this->user->getScopes());
        $user_of_his_company = self::createUserWithCredential($this->company->id,'Another User','123456789');
        $response = $this->delete('api/users/'.$user_of_his_company->id);
        $response->assertStatus (Response::HTTP_OK);
        $this->assertCount (0,User::where('id',$user_of_his_company->id)->get());
        $this->assertCount (0,Credential::where('user_id',$user_of_his_company->id)->get());
        $this->assertCount (0,UserRsaKey::where('user_id',$user_of_his_company->id)->get());
        $this->assertCount (0,UserRole::where ('user_id',$user_of_his_company->id)->get());
    }

    //NOT TESTS PART
    private function userData(){
        $role = $this->user->roles()->first();
        $credentials = $this->user->credentials()->first();
        return [
            'data' => [
                'user_id' => $this->user->id,
                'first_name' => decrypt ($this->user->first_name),
                'last_name' => decrypt ($this->user->last_name),
                'allow_log_in' => '1',
                'allow_log_in_for_humans' => 'Ja',
                'credentials' => [
                    [
                        'data' => [
                            'credential_id' => $credentials->id,
                            'username' => decrypt ($credentials->AES_256_username),
                            'username_type' => $credentials->username_type,
                            'valid_from_for_humans' => $credentials->valid_from->format('d-m-Y H:i'),
                            'valid_from' => $credentials->valid_from->format('d-m-Y H:i'),
                            'valid_until_for_humans' => $credentials->valid_until->format('d-m-Y H:i'),
                            'valid_until' => $credentials->valid_until->format('d-m-Y H:i'),
                            'created_at_for_humans' => $credentials->created_at->format('d-m-Y H:i'),
                            'created_at' => $credentials->created_at->format('d-m-Y H:i'),
                        ]
                    ]
                ],
                'company' => [
                    'data' => [
                        'company_id' => $this->company->id,
                        'name' => $this->company->name,
                        'description' => $this->company->description,
                        'company_website' => $this->company->company_website,
                    ]
                ],
                'roles' => [
                    [
                        'data' => [
                            'role_id' =>  $role->id,
                            'name' =>  $role->name,
                            'description' =>  $role->description,
                        ]
                    ]
                ],
                'scopes' => $this->user->getScopes()
            ]
        ];
    }

    private function requestData()
    {
        return [
            'first_name' => 'Test_first_name',
            'last_name' => 'Test_last_name',
            'company_id' => Company::factory()->create ()->id,
            'credentials' => [
                [
                    'username' => 'test_user_name',
                    'password' => 'Test12345678',
                    'password_confirmation' => 'Test12345678',
                    'valid_until' => '2019/12/04',
                ],
                [
                    'username' => 'test_username@test.com',
                    'password' => 'Test12345678',
                    'password_confirmation' => 'Test12345678',
                    'valid_until' => '2019/12/04',
                ],
            ],
        ];
    }
}
