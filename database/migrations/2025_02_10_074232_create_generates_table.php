<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generates', function (Blueprint $table) {
            $table->id();
            $table->string('app_name');
            $table->integer('discount_id');
            $table->string('conditions');
            $table->integer('expired_range');
            $table->integer('limit')->nullable()->default(null);
            $table->string('header_message')->nullable()->default(null);
            $table->string('success_message')->nullable()->default(null);
            $table->string('used_message')->nullable()->default(null);
            $table->string('fail_message')->nullable()->default(null);
            $table->string('app_url')->nullable()->default(null);
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generates');
    }
};
