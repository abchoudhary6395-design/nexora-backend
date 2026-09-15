<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deal_stages', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // lead, qualified, proposal, negotiation, won, lost
            $table->string('label');
            $table->string('color', 7)->default('#4F5EFF');
            $table->unsignedTinyInteger('default_probability')->default(0);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->boolean('is_won')->default(false);
            $table->boolean('is_lost')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_stages');
    }
};
