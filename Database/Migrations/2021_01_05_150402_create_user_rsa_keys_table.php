<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserRsaKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_rsa_keys', function (Blueprint $table) {
            $table->string('id',128)->unique ();
            $table->string('user_id',128);
            $table->string('public_key',700);
            $table->string('private_key',2500);
            $table->string('valid_from');
            $table->string('valid_until')->nullable ();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_rsa_keys');
    }
}
