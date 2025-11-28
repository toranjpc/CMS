<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Modules\User\Database\Seeders\UserSeeder;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id()->from(10000);
            $table->integer('f_id')->default(0);
            $table->tinyInteger('sex')->default(1);
            $table->string('ircode')->default(0)->nullable();
            $table->string('name')->nullable();
            $table->string('lastname')->nullable();
            $table->timestamp('birth')->nullable();
            $table->string('alias')->nullable();
            $table->string('url')->nullable();
            $table->string('username')->nullable();
            $table->string('mobile')->unique()->nullable();
            $table->timestamp('mobile_verified_at')->nullable();
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->integer('job')->default(0);
            $table->json('per')->nullable(); //->default("[]");
            $table->json('datas')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('useroptions', function (Blueprint $table) {
            $table->id();
            $table->integer('f_id')->default(0);
            $table->string('title', 100)->nullable();
            $table->string('kind', 100)->nullable(); //UserCategory:[admin,editor,user] , UserGroup:[seller1,seller2,...]
            $table->json('option')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->unique(['title', 'kind']);
        });

        Schema::create('Extdatas', function (Blueprint $table) {
            $table->id();
            $table->integer('f_id')->default(0);
            $table->integer('m_id')->default(0);
            $table->integer('s_id')->default(0);
            $table->string('title')->nullable();
            $table->string('kind')->nullable(); //UserCategory:[admin,editor,user] , UserGroup:[seller1,seller2,...]
            $table->json('datas')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
        });


        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        (new UserSeeder())->run();
    }


    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('useroptions');
        Schema::dropIfExists('ExtData');

        Schema::dropIfExists('password_reset_tokens');
    }
};
