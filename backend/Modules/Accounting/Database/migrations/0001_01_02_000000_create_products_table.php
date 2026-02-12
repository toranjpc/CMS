<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\User\Models\User;
use Modules\Accounting\Models\Invoice;

use Modules\Accounting\Database\Seeders\ProductSeeder;
use Modules\Accounting\Models\Product;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->nullable()->constrained()->nullOnDelete();

            $table->string('title')->nullable();
            $table->string('barcode')->nullable()->unique();

            $table->json('album')->nullable();
            $table->string('tags')->nullable();
            $table->text('des')->nullable();

            $table->json('form')->nullable();
            $table->tinyInteger('tax_rate')->default(10);
            $table->tinyInteger('min_buy')->default(0);
            $table->tinyInteger('max_buy')->default(0);
            $table->tinyInteger('alert')->default(0);

            $table->tinyInteger('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('product_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->nullable()->constrained()->nullOnDelete();
            $table->string('title')->nullable();

            $table->unsignedBigInteger('f_id')->nullable();
            $table->foreign('f_id')->references('id')->on('products')->cascadeOnDelete();

            $table->integer('firstWarehouse')->default(0);
            $table->integer('current_stock')->default(0);
            $table->decimal('firstPrice', 15, 2)->default(0);
            $table->decimal('sell_price', 15, 2)->default(0);

            $table->boolean('convertUnit')->default(false);
            $table->integer('UnitNumber')->default(0);
            $table->unsignedBigInteger('selectConvertUnit')->nullable();
            $table->foreign('selectConvertUnit')->references('id')->on('product_options')->nullOnDelete();


            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });


        Schema::create('product_options', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('f_id')->nullable();
            $table->foreign('f_id')->references('id')->on('product_options')->nullOnDelete();

            $table->string('title')->nullable();
            $table->text('des')->nullable();
            $table->json('option')->nullable();

            $table->enum('kind', ['category', 'option', 'brand', 'unit', 'warehouse'])->nullable();
            $table->unique(['title', 'f_id', 'kind']);

            $table->tinyInteger('status')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });


        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();

            // $table->foreignId('user_id')->nullable();
            $table->foreignIdFor(User::class)->constrained()->restrictOnDelete();

            $table->enum('type', ['buy', 'sell'])->comment('buy = خرید | sell = فروش');

            // $table->foreignId('party_id')->comment('مشتری یا فروشنده');
            $table->foreignIdFor(User::class, 'party_id')->constrained()->restrictOnDelete();

            $table->date('date');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->enum('status', ['draft', 'confirmed', 'paid', 'cancelled'])->default('draft');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Invoice::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Product::class)->constrained()->cascadeOnDelete();
            $table->foreignId('product_item_id')->nullable()->constrained('product_items')->nullOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total_price', 15, 2);
            $table->string('description')->nullable();
            $table->enum('type', ['buy', 'sell'])->comment('buy = خرید | sell = فروش');
            $table->timestamps();
        });
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            // نوع سند
            $table->enum('type', ['receive', 'payment'])->comment('receive = دریافت | payment = پرداخت');
            // شماره سند
            $table->string('transaction_number')->unique();
            // کاربر
            // $table->foreignId('user_id')->nullable();
            $table->foreignIdFor(User::class)->constrained()->restrictOnDelete();
            // طرف حساب
            // $table->foreignId('party_id')->comment('مشتری یا فروشنده');
            $table->foreignIdFor(User::class, 'party_id')->constrained()->restrictOnDelete();
            // مبلغ
            $table->decimal('amount', 15, 2);
            // روش پرداخت
            $table->enum('payment_method', ['cash', 'card', 'bank', 'cheque'])->default('cash');
            // ارتباط با فاکتور (اختیاری)
            // $table->foreignId('invoice_id')->nullable();
            $table->foreignIdFor(Invoice::class)->nullable()->constrained()->restrictOnDelete();
            // تاریخ
            $table->date('transaction_date');
            // توضیحات
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });


        (new ProductSeeder())->run();
    }


    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_items');
        Schema::dropIfExists('product_options');

        Schema::dropIfExists('invoices');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('transactions');
    }
};
