<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->index();
            $table->integer('actor_id')->nullable()->index();
            $table->string('type', 50)->index();
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->string('item_type', 50)->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->integer('is_read')->default(0)->index();
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
