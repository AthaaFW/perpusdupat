<?php

namespace App\Http\Controllers;

use App\Http\Requests\Book\ImportBookRequest;
use App\Http\Requests\Book\StoreBookRequest;
use App\Http\Requests\Book\UpdateBookRequest;
use App\Models\Book;
use App\Models\Category;
use App\Services\ExcelReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BookController extends Controller
{
    /**
     * Display a listing of books with live search support.
     */
    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('viewAny', Book::class);

        $search = $request->query('search');
        $categoryId = $request->query('category_id');
        $availability = $request->query('availability');

        $query = Book::with('category:id,name,code')
            ->search($search);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($availability === 'available') {
            $query->available();
        } elseif ($availability === 'out_of_stock') {
            $query->where('available_stock', '<=', 0);
        }

        $books = $query->latest('id')->paginate(12)->withQueryString();

        if ($request->wantsJson() || $request->header('X-Alpine-Request')) {
            return response()->json([
                'books' => $books->items(),
                'pagination' => [
                    'current_page' => $books->currentPage(),
                    'last_page' => $books->lastPage(),
                    'total' => $books->total(),
                    'has_more' => $books->hasMorePages(),
                ],
            ]);
        }

        $categories = Category::orderBy('name')->get(['id', 'name', 'code']);

        return view('books.index', compact('books', 'categories', 'search', 'categoryId', 'availability'));
    }

    /**
     * Show the form for creating a new book.
     */
    public function create(): View
    {
        Gate::authorize('create', Book::class);

        $categories = Category::orderBy('name')->get(['id', 'name', 'code']);

        return view('books.create', compact('categories'));
    }

    /**
     * Store a newly created book in storage.
     */
    public function store(StoreBookRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('books', 'public');
            $validated['cover_image'] = $path;
        }

        $validated['available_stock'] = $validated['total_stock'];

        Book::create($validated);

        return redirect()->route('books.index')->with('success', 'Buku baru berhasil ditambahkan ke katalog.');
    }

    /**
     * Show the form for editing the specified book.
     */
    public function edit(Book $book): View
    {
        Gate::authorize('update', $book);

        $categories = Category::orderBy('name')->get(['id', 'name', 'code']);

        return view('books.edit', compact('book', 'categories'));
    }

    /**
     * Update the specified book in storage.
     */
    public function update(UpdateBookRequest $request, Book $book): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('cover_image')) {
            if ($book->cover_image && Storage::disk('public')->exists($book->cover_image)) {
                Storage::disk('public')->delete($book->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')->store('books', 'public');
        }

        // Adjust available_stock proportional to total_stock change
        $stockDifference = $validated['total_stock'] - $book->total_stock;
        $newAvailableStock = max(0, $book->available_stock + $stockDifference);
        $validated['available_stock'] = $newAvailableStock;

        $book->update($validated);

        return redirect()->route('books.index')->with('success', 'Data buku berhasil diperbarui.');
    }

    /**
     * Remove the specified book from storage.
     */
    public function destroy(Book $book): RedirectResponse
    {
        Gate::authorize('delete', $book);

        $hasActiveLoans = $book->loans()->whereIn('status', ['dipinjam', 'terlambat', 'menunggu_persetujuan'])->exists();

        if ($hasActiveLoans) {
            return back()->with('error', 'Buku tidak dapat dihapus karena sedang dipinjam atau menunggu persetujuan.');
        }

        if ($book->cover_image && Storage::disk('public')->exists($book->cover_image)) {
            Storage::disk('public')->delete($book->cover_image);
        }

        $book->delete();

        return redirect()->route('books.index')->with('success', 'Buku berhasil dihapus dari katalog.');
    }

    /**
     * Handle bulk XLSX import of books.
     */
    public function import(ImportBookRequest $request, ExcelReportService $excelService): RedirectResponse
    {
        $file = $request->file('file');
        $storedPath = $file->storeAs('imports', 'import_'.time().'_'.$file->getClientOriginalName());
        $fullPath = Storage::path($storedPath);

        $result = $excelService->importBooksChunked($fullPath);

        // Remove uploaded temp file
        Storage::delete($storedPath);

        $message = "Import selesai: {$result['imported']} buku baru ditambahkan, {$result['updated']} buku diperbarui.";

        if ($result['failed'] > 0) {
            $message .= " ({$result['failed']} baris gagal/dilewati).";

            return redirect()->route('books.index')
                ->with('warning', $message)
                ->with('import_errors', $result['errors']);
        }

        return redirect()->route('books.index')->with('success', $message);
    }

    /**
     * Download the standard Excel import template.
     */
    public function downloadTemplate(ExcelReportService $excelService): BinaryFileResponse
    {
        Gate::authorize('import', Book::class);

        $templatePath = $excelService->generateImportTemplate();

        return response()->download($templatePath, 'books_import_template.xlsx')->deleteFileAfterSend(true);
    }
}
