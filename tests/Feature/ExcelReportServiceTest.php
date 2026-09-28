<?php

use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\User;
use App\Models\Visit;
use App\Services\ExcelReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('generates print-ready visit report xlsx', function () {
    $user = User::factory()->create(['nis_nip' => 'TEST001', 'role' => 'murid']);
    Visit::create([
        'user_id' => $user->id,
        'visit_date' => Carbon::today()->toDateString(),
        'purpose' => 'Membaca',
    ]);

    $service = new ExcelReportService;
    $filePath = $service->generateVisitReport();

    expect(file_exists($filePath))->toBeTrue();
    expect(filesize($filePath))->toBeGreaterThan(1000);

    unlink($filePath);
});

test('generates print-ready loan report xlsx', function () {
    $user = User::factory()->create(['nis_nip' => 'TEST002', 'role' => 'murid']);
    $category = Category::create(['code' => '800', 'name' => 'Sastra']);
    $book = Book::create([
        'category_id' => $category->id,
        'isbn' => '9780000000001',
        'title' => 'Buku Uji Excel',
        'total_stock' => 5,
        'available_stock' => 4,
    ]);

    Loan::create([
        'user_id' => $user->id,
        'book_id' => $book->id,
        'loan_date' => Carbon::today()->subDays(3)->toDateString(),
        'due_date' => Carbon::today()->addDays(4)->toDateString(),
        'status' => 'dipinjam',
    ]);

    $service = new ExcelReportService;
    $filePath = $service->generateLoanReport();

    expect(file_exists($filePath))->toBeTrue();
    expect(filesize($filePath))->toBeGreaterThan(1000);

    unlink($filePath);
});

test('generates book import template', function () {
    $service = new ExcelReportService;
    $filePath = $service->generateImportTemplate();

    expect(file_exists($filePath))->toBeTrue();
    expect(filesize($filePath))->toBeGreaterThan(1000);

    unlink($filePath);
});
