<?php
namespace Modules\Accounts\Tests;

use Modules\Accounts\Entities\Company\Company;

class RoleTestCase extends PassportTestCase
{
    protected $companies_ids;

    public function requestData()
    {
        $this->companies_ids = Company::all ()->pluck ('id')->toArray ();
        return [
            'name' => 'New Role',
            'description' =>  'Role Description',
            'is_custom' => 1,
            'scopes' => [
                [
                    'scope_id' => $this->user_account_list_all->id,
                    'companies_ids' => $this->companies_ids,
                ],
                [
                    'scope_id' => $this->user_account_add_all->id,
                    'companies_ids' => $this->companies_ids,
                ],
            ],
        ];
    }
}
