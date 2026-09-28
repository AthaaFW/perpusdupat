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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('isbn')->unique()->index();
            $table->string('title')->index();
            $table->string('author')->nullable()->index();
            $table->string('publisher')->nullable();
            $table->unsignedSmallInteger('publication_year')->nullable()->index();
            $table->string('rack_location')->nullable()->index();
            $table->unsignedInteger('total_stock')->default(0);
            $table->unsignedInteger('available_stock')->default(0);
            $table->string('cover_image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
