<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KioskController extends Controller
{
    /**
     * Show self-service kiosk check-in screen for Student Client PC station.
     */
    public function index(): View
    {
        $today = Carbon::today()->toDateString();

        $todayVisits = Visit::with('user:id,name,nis_nip')
            ->whereDate('visit_date', $today)
            ->latest('id')
            ->take(15)
            ->get();

        $visitCountToday = Visit::whereDate('visit_date', $today)->count();

        return view('kiosk.index', compact('todayVisits', 'visitCountToday'));
    }

    /**
     * Fast AJAX check-in endpoint for kiosk.
     */
    public function checkIn(Request $request): JsonResponse
    {
        $request->validate([
            'nis' => ['required', 'string'],
            'purpose' => ['required', 'string', 'max:50'],
        ]);

        $nis = trim($request->input('nis'));
        $user = User::where('nis_nip', $nis)->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => "NIS '{$nis}' tidak ditemukan dalam database perpustakaan.",
            ], 404);
        }

        $today = Carbon::today()->toDateString();

        $visit = Visit::create([
            'user_id' => $user->id,
            'visit_date' => $today,
            'purpose' => $request->input('purpose'),
            'notes' => 'Presensi Mandiri Kiosk LAN',
        ]);

        $todayVisitsCount = Visit::whereDate('visit_date', $today)->count();

        return response()->json([
            'success' => true,
            'message' => "Selamat datang, {$user->name}! Presensi berhasil dicatat.",
            'data' => [
                'id' => $visit->id,
                'name' => $user->name,
                'nis' => $user->nis_nip,
                'purpose' => $visit->purpose,
                'time' => $visit->created_at->format('H:i'),
                'total_today' => $todayVisitsCount,
            ],
        ]);
    }
}
