<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTokenKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('token_keys', function (Blueprint $table) {
            $table->id();
            $table->string('public_key', 1023);
            $table->string('private_key', 4095);
            $table->dateTime('valid_from');
            $table->dateTime('valid_until');
            $table->smallInteger('revoked')->default(0);
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
        Schema::dropIfExists('token_keys');
    }
}
