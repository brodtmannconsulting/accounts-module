<?php

namespace Modules\Accounts\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class GenerateTokenKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokenKeys:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Public and Private Keys and revoke old keys';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
//        $public_key = file_get_contents('../storage/oauth-public.key');
//        $private_key = file_get_contents('../storage/oauth-private.key');

        $public_key = file_get_contents(storage_path().'/oauth-public.key');
        $private_key = file_get_contents(storage_path().'/oauth-private.key');
//        dd($public_key,$private_key);
        $token_keys = TokenKey::create([
            'public_key' => $public_key,
            'private_key' => $private_key,
            'valid_from' => date("Y/m/d H:i"),
            'valid_until' => date("Y/m/d H:i", strtotime("+1 Month", time()))
        ]);
        $this->info('Successfully added new TokenKeys!'. $token_keys->public_key);
    }
}
