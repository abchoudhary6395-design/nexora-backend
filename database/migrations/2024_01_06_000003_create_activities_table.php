<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // lead_created, deal_won, invoice_paid, task_completed...
            $table->string('description'); // human-readable summary rendered in the feed
            $table->morphs('subject'); // the Lead/Deal/Customer/etc. this activity is about
            $table->json('meta')->nullable(); // extra structured data (old/new values, etc.)
            $table->timestamps();

            $table->index(['type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
