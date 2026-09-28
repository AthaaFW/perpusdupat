<?php

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('librarian can upload book with cover image', function () {
    Storage::fake('public');

    $librarian = User::factory()->create(['nis_nip' => 'LIB999', 'role' => 'pustakawan']);
    $category = Category::create(['code' => '800', 'name' => 'Sastra']);

    $file = UploadedFile::fake()->image('buku_cover.jpg', 400, 600);

    $response = $this->actingAs($librarian)->post('/books', [
        'category_id' => $category->id,
        'isbn' => '9781234567890',
        'title' => 'Buku Sampul Baru',
        'author' => 'Penulis Sampul',
        'total_stock' => 5,
        'cover_image' => $file,
    ]);

    $response->assertRedirect('/books');

    $book = Book::where('isbn', '9781234567890')->first();
    expect($book)->not->toBeNull();
    expect($book->cover_image)->not->toBeNull();
    expect($book->cover_url)->toContain('storage/'.$book->cover_image);

    Storage::disk('public')->assertExists($book->cover_image);
});

test('updating book cover deletes old image file and stores new', function () {
    Storage::fake('public');

    $librarian = User::factory()->create(['nis_nip' => 'LIB999', 'role' => 'pustakawan']);
    $category = Category::create(['code' => '800', 'name' => 'Sastra']);

    $oldFile = UploadedFile::fake()->image('old_cover.jpg');
    $oldPath = $oldFile->store('books', 'public');

    $book = Book::create([
        'category_id' => $category->id,
        'isbn' => '9781234567891',
        'title' => 'Buku Uji Cover',
        'total_stock' => 3,
        'available_stock' => 3,
        'cover_image' => $oldPath,
    ]);

    Storage::disk('public')->assertExists($oldPath);

    $newFile = UploadedFile::fake()->image('new_cover.jpg');

    $response = $this->actingAs($librarian)->put("/books/{$book->id}", [
        'category_id' => $category->id,
        'isbn' => '9781234567891',
        'title' => 'Buku Uji Cover Updated',
        'total_stock' => 3,
        'cover_image' => $newFile,
    ]);

    $response->assertRedirect('/books');

    $book->refresh();
    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($book->cover_image);
});

test('mobile scanner api supports cover_image url or path', function () {
    $scannerUser = User::factory()->create(['nis_nip' => 'SCANNER-DEVICE-01', 'role' => 'pustakawan']);
    Sanctum::actingAs($scannerUser);

    $category = Category::create(['code' => '800', 'name' => 'Sastra']);

    $response = $this->postJson('/api/v1/books/isbn-scan', [
        'isbn' => '9787777777777',
        'title' => 'Buku dari Scanner dengan Cover',
        'category_id' => $category->id,
        'stock' => 2,
        'cover_image' => 'https://example.com/cover.jpg',
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'data' => [
                'cover_image' => 'https://example.com/cover.jpg',
                'cover_url' => 'https://example.com/cover.jpg',
            ],
        ]);
});
