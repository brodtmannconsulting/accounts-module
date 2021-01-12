## Accounts module by [Brodtmann-consulting](https://brodtmann-consulting.com/)

## About Accounts Module :information_source:

This module contains User, Credential, Company, Role and Scope blocs. The Auth is made with the help of laravel/passport and also checks for Google Recaptcha. 

## Requirements :warning:

1) Composer
2) PHP
3) Laravel
4) [Laravel Passport](https://laravel.com/docs/8.x/passport)
5) [Laravel Modules](https://nwidart.com/laravel-modules/v6/installation-and-setup)
6) [Laravel Module Installer](https://github.com/joshbrw/laravel-module-installer)

## Installation and Setup :rocket:

Before you start to you need to install all the **requirements**!

0) First, add these lines of code to your composer.json:

        "repositories": [
                {
                    "type": "vcs",
                    "url": "git@github.com:brodtmannconsulting/accounts-module"
                }
            ],
            
1) Run: `composer require brodtmannconsulting/accounts-module` in your Laravel project. To install this package you need to be a **collaborator** of this repo and also have an [access token](https://docs.github.com/en/free-pro-team@latest/github/authenticating-to-github/creating-a-personal-access-token).
2) Also publish Laravel Passport tables: `php artisan vendor:publish --tag=passport-migrations` and **delete them**, because all the necessary tables are located in Accounts Modules:
        
        DELETE:
        2016_06_01_000001_create_oauth_auth_codes_table.php
        2016_06_01_000002_create_oauth_access_tokens_table.php
        2016_06_01_000003_create_oauth_refresh_tokens_table.php
        2016_06_01_000004_create_oauth_clients_table.php
        2016_06_01_000005_create_oauth_personal_access_clients_table.php
        
3) Delete following table: `create_users_table` in your root project
4) Run: `php artisan module:migrate Accounts` and `php artisan module:seed Accounts`
5) After that run: `php artisan passport:install` and `php artisan passport:keys` if you haven't done it yet.
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
8) And also uncomment these lines of code in your phpunit.xml file:

         <server name="DB_CONNECTION" value="sqlite"/>
         <server name="DB_DATABASE" value=":memory:"/>
                 
9) And the last step is to run `php artisan test`
10) If all the **tests have passed** the module was installed successfully! :tada:

## Documentation :book:

[API Documentation](https://documenter.getpostman.com/view/11679015/TVzRFdDY)
