<?php

namespace App\Http\Controllers;

use App\Http\Requests\Visit\StoreVisitRequest;
use App\Models\User;
use App\Models\Visit;
use App\Services\ExcelReportService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VisitController extends Controller
{
    /**
     * Display a listing of library visits.
     */
    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('viewAny', Visit::class);

        $user = $request->user();
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->toDateString());
        $search = $request->query('search');

        $query = Visit::with('user:id,name,nis_nip,role')
            ->dateRange($startDate, $endDate);

        // Scope to student's own records if role is murid
        if ($user->isMurid()) {
            $query->where('user_id', $user->id);
        } elseif ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nis_nip', 'like', "%{$search}%");
            });
        }

        $visits = $query->latest('visit_date')->latest('id')->paginate(25)->withQueryString();

        if ($request->wantsJson() || $request->header('X-Alpine-Request')) {
            return response()->json([
                'visits' => $visits->items(),
                'total' => $visits->total(),
            ]);
        }

        return view('visits.index', compact('visits', 'startDate', 'endDate', 'search'));
    }

    /**
     * Store a newly recorded library visit.
     */
    public function store(StoreVisitRequest $request): RedirectResponse|JsonResponse
    {
        Gate::authorize('create', Visit::class);

        $currentUser = $request->user();
        $targetUserId = null;

        if ($request->filled('nis_nip')) {
            $foundUser = User::where('nis_nip', trim($request->input('nis_nip')))->first();
            if (! $foundUser) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'NIS/NIP tidak terdaftar dalam sistem.',
                    ], 422);
                }

                return back()->with('error', 'NIS/NIP tidak terdaftar dalam sistem.');
            }
            $targetUserId = $foundUser->id;
            $visitorName = $foundUser->name;
        } else {
            $targetUserId = $currentUser->id;
            $visitorName = $currentUser->name;
        }

        $today = Carbon::today()->toDateString();

        // Check if student already checked in today
        $alreadyVisited = Visit::where('user_id', $targetUserId)
            ->whereDate('visit_date', $today)
            ->exists();

        $visit = Visit::create([
            'user_id' => $targetUserId,
            'visit_date' => $today,
            'purpose' => $request->input('purpose', 'Membaca'),
            'notes' => $request->input('notes'),
        ]);

        $message = "Presensi kunjungan berhasil dicatat. Selamat datang, {$visitorName}!";

        if ($alreadyVisited) {
            $message = "Presensi kunjungan ke-2 hari ini berhasil dicatat untuk {$visitorName}.";
        }

        if ($request->wantsJson() || $request->header('X-Alpine-Request')) {
            $visit->load('user:id,name,nis_nip,role');

            return response()->json([
                'success' => true,
                'message' => $message,
                'visit' => $visit,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Export visit reports to print-ready XLSX.
     */
    public function export(Request $request, ExcelReportService $excelService): BinaryFileResponse
    {
        Gate::authorize('export', Visit::class);

        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $filePath = $excelService->generateVisitReport($startDate, $endDate);
        $fileName = 'laporan_kunjungan_'.date('Ymd_His').'.xlsx';

        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }

    /**
     * Remove the specified visit log.
     */
    public function destroy(Visit $visit): RedirectResponse
    {
        Gate::authorize('delete', $visit);

        $visit->delete();

        return back()->with('success', 'Catatan kunjungan berhasil dihapus.');
    }
}
