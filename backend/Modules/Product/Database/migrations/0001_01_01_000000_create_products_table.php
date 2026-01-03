<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\User\Models\User;

use Modules\Product\Database\Seeders\ProductSeeder;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->nullable()->constrained()->nullOnDelete();

            $table->unsignedBigInteger('f_id')->nullable();
            $table->foreign('f_id')->references('id')->on('products')->cascadeOnDelete();

            $table->string('title')->nullable();
            $table->json('album')->nullable();
            $table->string('tags')->nullable();
            $table->text('des')->nullable();
            $table->integer('firstWarehouse')->nullable()->default(0);
            $table->decimal('firstPrice', 15, 2)->nullable()->default(0);
            $table->decimal('sell_price', 15, 2)->nullable()->default(0);
            $table->json('form')->nullable();

            $table->tinyInteger('tax_rate')->nullable()->default(10);
            $table->tinyInteger('min_buy')->nullable()->default(0);
            $table->tinyInteger('max_buy')->nullable()->default(0);
            $table->tinyInteger('alert')->nullable()->default(0);

            $table->tinyInteger('status')->nullable()->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('product_options', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('f_id')->nullable();
            $table->foreign('f_id')->references('id')->on('product_options')->nullOnDelete();

            $table->string('title')->nullable();
            $table->text('des')->nullable();
            $table->json('option')->nullable();

            $table->enum('kind', ['category', 'option', 'brand','unit'])->nullable();
            $table->unique(['title', 'f_id', 'kind']);

            $table->tinyInteger('status')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        (new ProductSeeder())->run();
    }


    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_options');
    }
};
