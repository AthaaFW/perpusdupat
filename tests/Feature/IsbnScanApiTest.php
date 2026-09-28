<?php

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('unauthenticated request to isbn-scan endpoint is rejected with 401', function () {
    $response = $this->postJson('/api/v1/books/isbn-scan', [
        'isbn' => '9786020332956',
        'title' => 'Test Book',
        'stock' => 5,
    ]);

    $response->assertStatus(401);
});

test('scanner node creates new book when isbn does not exist', function () {
    $scannerUser = User::where('nis_nip', 'SCANNER-DEVICE-01')->first()
        ?? User::factory()->create(['nis_nip' => 'SCANNER-DEVICE-01', 'role' => 'pustakawan']);

    Sanctum::actingAs($scannerUser);

    $category = Category::first() ?? Category::create(['code' => '800', 'name' => 'Sastra']);

    $payload = [
        'isbn' => '9789999999999',
        'title' => 'Buku Baru Test Scanner',
        'author' => 'Penulis Uji',
        'publisher' => 'Penerbit Uji',
        'publication_year' => 2024,
        'category_id' => $category->id,
        'rack_location' => 'R-99',
        'stock' => 5,
    ];

    $response = $this->postJson('/api/v1/books/isbn-scan', $payload);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'data' => [
                'isbn' => '9789999999999',
                'title' => 'Buku Baru Test Scanner',
                'total_stock' => 5,
                'available_stock' => 5,
                'action' => 'book_created',
            ],
        ]);

    $this->assertDatabaseHas('books', [
        'isbn' => '9789999999999',
        'total_stock' => 5,
        'available_stock' => 5,
    ]);
});

test('scanner node increments stock when isbn already exists', function () {
    $scannerUser = User::where('nis_nip', 'SCANNER-DEVICE-01')->first()
        ?? User::factory()->create(['nis_nip' => 'SCANNER-DEVICE-01', 'role' => 'pustakawan']);

    Sanctum::actingAs($scannerUser);

    $category = Category::first() ?? Category::create(['code' => '800', 'name' => 'Sastra']);
    $book = Book::first() ?? Book::create([
        'isbn' => '9786020332956',
        'title' => 'Bumi Manusia',
        'category_id' => $category->id,
        'total_stock' => 5,
        'available_stock' => 5,
    ]);

    $initialTotalStock = $book->total_stock;
    $initialAvailableStock = $book->available_stock;

    $payload = [
        'isbn' => $book->isbn,
        'title' => $book->title,
        'stock' => 3,
    ];

    $response = $this->postJson('/api/v1/books/isbn-scan', $payload);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'isbn' => $book->isbn,
                'total_stock' => $initialTotalStock + 3,
                'available_stock' => $initialAvailableStock + 3,
                'action' => 'stock_incremented',
            ],
        ]);

    $book->refresh();
    expect($book->total_stock)->toBe($initialTotalStock + 3);
    expect($book->available_stock)->toBe($initialAvailableStock + 3);
});
