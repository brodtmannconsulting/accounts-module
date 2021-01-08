<?php
namespace Modules\Accounts\Tests;

class CompanyTestCase extends PassportTestCase
{

    public function requestData()
    {
        return [
            'name' =>  'NEW COMPANY',
            'description' => 'new description',
            'company_website' =>  'https://www.bahn.de/p/view/index.shtml',
        ];
    }
}
