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
        
            Schema::create('telegram_accounts', function (Blueprint $table) {
                $table->id();
                $table->string('phone')->unique();
                $table->string('session_file'); // ex : 22501010101.session
                $table->boolean('authorized')->default(false);
                $table->timestamps();
            });
        

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_accounts');
    }
};
