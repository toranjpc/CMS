<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('apps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('app_id')->nullable()->constrained('apps')->nullOnDelete();
            $table->foreignId('uid')->nullable()->constrained('users')->nullOnDelete();
            $table->string('url')->nullable()->unique();
            $table->string('title')->nullable();
            $table->text('sett')->nullable(); //->default('["icon" => "icon.png", "favicon" => "favicon.png"]')
            $table->tinyInteger('status')->default(1);
            $table->date('expiry_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apps');
    }
};
