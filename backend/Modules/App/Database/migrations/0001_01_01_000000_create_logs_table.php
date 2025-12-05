<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\User\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->nullable()->constrained()->cascadeOnDelete();
            $table->string('table_name', 100)->nullable();
            $table->bigInteger('table_id')->default(0);
            $table->enum('type', ['create', 'update', 'delete', 'restore', 'login', 'logout', 'password_change', 'export', 'import'])->nullable();
            $table->longText('data');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
