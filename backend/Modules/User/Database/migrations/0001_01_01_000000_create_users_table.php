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

            $table->unsignedBigInteger('f_id')->nullable();
            $table->foreign('f_id')->references('id')->on('users')->nullOnDelete();

            $table->tinyInteger('sex')->default(1);
            $table->string('ircode')->default(0)->nullable();
            $table->string('name')->nullable();
            $table->string('lastname')->nullable();
            $table->string('alias')->nullable();
            $table->timestamp('birth')->nullable();
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('mobile')->unique()->nullable();
            $table->timestamp('mobile_verified_at')->nullable();

            $table->foreignId('job')->nullable()->constrained('useroptions', 'id')->nullOnDelete();
            $table->json('per')->nullable();

            $table->json('datas')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('useroptions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('f_id')->nullable();
            $table->foreign('f_id')->references('id')->on('useroptions')->nullOnDelete();

            $table->string('title', 100)->nullable();

            $table->enum('kind', ['UserCategory', 'job', 'plan'])->nullable(); //UserCategory:[admin,editor,user] , UserGroup:[seller1,seller2,...]
            $table->unique(['title', 'f_id', 'kind']);

            $table->json('option')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('extdatas', function (Blueprint $table) {
            $table->id();
            $table->integer('f_id')->default(0);
            $table->integer('m_id')->default(0);
            $table->integer('s_id')->default(0);
            $table->string('title')->nullable();
            $table->string('kind')->nullable();
            $table->json('datas')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
        });


        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('mobile')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        (new UserSeeder())->run();
    }


    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('extdatas');
        Schema::dropIfExists('useroptions');
        Schema::dropIfExists('users');
    }
};
