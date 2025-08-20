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
        Schema::create('result_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('name');
            $table->integer('age');
            $table->string('gender');
            $table->integer('heart_rate');
            $table->string('hasil');
            $table->float('probabilitas', 8, 4); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('result_predictions');
    }
};
