## Accounts module by [Brodtmann-consulting](https://brodtmann-consulting.com/)

## About Accounts Module

This module contains User, Credential, Company, Role and Scope blocs. The Auth is made with the help of laravel/passport and also checks for Google Recaptcha. 

## Requirements

1) Composer
2) PHP
3) Laravel
4) Laravel Passport

## Installation Steps :rocket:

1) Run: `composer require maxprimak/accounts-module` in your Laravel project
2) Then install laravel/passport if you haven't done it yet: `composer require laravel/passport`. More info about [Laravel Passport](https://laravel.com/docs/8.x/passport)
3) Delete following table: `create_users_table` in your root project
3) Run: `php artisan migrate`
3) After that run: `php artisan passport:install`
4) Run: `php artisan passport:keys` and Laravel Passport set up should be finished.
5) Then run: `php artisan module:migrate Accounts` and `php artisan module:seed Accounts` to migrate and seed all the tables.
6) Also add new provider to your config/auth.php:
        
        'credentials' => [
            'driver' => 'eloquent',
            'model' => \Modules\Accounts\Entities\Credential\Credential::class,
        ],
                
    and replace 'api' guard with the following code:

        'api' => [
             'driver' => 'passport',
             'provider' => 'credentials',
             'hash' => false,
         ], 

7) Then go to phpunit.xml in your root project and replace the existing part of code with the new lines:
  
        <testsuites> 
            <testsuite name="Feature">
                <directory suffix="Test.php">./Modules/**/Tests/Feature</directory>
            </testsuite>
            <testsuite name="Unit">
                <directory suffix="Test.php">./Modules/**/Tests/Unit</directory>
            </testsuite>
        </testsuites>

7) And the last step is to run `php artisan test`
8) If all the **tests have passed** the module was installed successfully! :tada:
