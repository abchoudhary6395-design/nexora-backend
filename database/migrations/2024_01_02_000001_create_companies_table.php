<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('industry', 100)->nullable();
            $table->string('website')->nullable();
            $table->string('logo')->nullable();
            $table->unsignedInteger('employees')->nullable();
            $table->decimal('annual_revenue', 14, 2)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['industry', 'country']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
