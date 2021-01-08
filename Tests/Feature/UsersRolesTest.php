<?php

namespace Modules\Accounts\Tests\Feature;

use Illuminate\Http\Response;
use Modules\Accounts\Entities\Scope\Scope;
use Modules\Accounts\Tests\PassportTestCase;

class UsersRolesTest extends PassportTestCase
{
    protected $access_to_write_user_roles;

    /**
     * @test
     */
    public function a_user_with_scope_write_user_roles_can_add_role_to_a_user()
    {
        $this->withoutExceptionHandling ();
        $this->access_to_write_user_roles = Scope::factory()->create (['name' => 'access_to_write_user_roles']);
        $response = $this->post('api/users_roles',$this->requestData());
        $response->assertStatus (Response::HTTP_CREATED)->assertJson ([
            'data' => [
                'user_id' => $this->user->id,
                'roles' => [
                    [
                        'data' => [
                            'role_id' =>  $this->user->roles ()->firstOrFail()->id,
                            'name' =>  $this->user->roles ()->firstOrFail()->name,
                            'description' =>  $this->user->roles ()->firstOrFail()->description,
                        ]
                    ]
                ],
                'scopes' => $this->user->getScopes()
            ]
        ]);

        $this->assertDatabaseHas ('user_roles',[
            'user_id' => $this->user->id,
            'role_id' => $this->user->roles ()->firstOrFail()->id
        ]);

        $this->assertDatabaseHas ('role_scopes',[
            'scope_id' => $this->access_to_write_user_roles->id,
            'role_id' => $this->user->roles ()->firstOrFail()->id,
            'company_id' => $this->user->company_id,
        ]);
    }

    private function requestData()
    {
        return [
            'user_id' => $this->user->id,
            'roles' => [
                [
                    'role_id' => $this->user->roles ()->firstOrFail()->id,
                    'scopes' => [
                        [
                            'scope_id' => $this->access_to_write_user_roles->id
                        ],
                    ],
                ],
                [
                    'role_id' => $this->user->roles ()->firstOrFail()->id
                ]
            ]
        ];
    }
}
