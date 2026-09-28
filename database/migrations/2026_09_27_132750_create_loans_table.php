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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->index();
            $table->foreignId('book_id')->constrained('books')->cascadeOnDelete()->index();
            $table->date('loan_date')->index();
            $table->date('due_date')->index();
            $table->date('return_date')->nullable()->index();
            $table->enum('status', ['menunggu_persetujuan', 'dipinjam', 'kembali', 'terlambat'])->default('menunggu_persetujuan')->index();
            $table->decimal('fine_amount', 10, 2)->default(0);
            $table->text('librarian_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
