<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Period;
use Illuminate\Http\Request;

class PerformanceController extends Controller
{
    public function index()
    {
        // 1. Ambil periode aktif
        $activePeriod = Period::where('is_active', true)->first();

        if (!$activePeriod) {
            return view('performance.index', ['teamData' => [], 'activePeriod' => null]);
        }

        // 2. Tarik semua user yang aktif beserta kalkulasi proyek yang mereka dapatkan (sebagai Finder)
        $targetMinimal = $activePeriod->target_mitra_per_user; // Default: 1 mitra

        $teamData = User::where('is_active', true)
            ->with(['projectsAsFinder' => function($query) use ($activePeriod) {
                $query->where('period_id', $activePeriod->id);
            }])
            ->get()
            ->map(function ($user) use ($targetMinimal) {
                // Hitung total mitra yang dibawa user pada periode ini
                $totalBrought = $user->projectsAsFinder->count();
                
                // Hitung persentase pencapaian KPI
                $user->kpi_progress = $totalBrought >= $targetMinimal ? 100 : round(($totalBrought / $targetMinimal) * 100);
                $user->total_mitra = $totalBrought;
                $user->status_kpi = $totalBrought >= $targetMinimal ? 'Lolos Target' : 'Belum Memenuhi';

                return $user;
            });

        return view('performance.index', compact('teamData', 'activePeriod'));
    }

    /**
     * LOGIKA DEWA: Reset Periode & Buka Kuartal Baru
     */
    public function closePeriod(Request $request)
    {
        $request->validate([
            'new_period_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        // 1. Nonaktifkan semua periode yang sedang berjalan
        Period::where('is_active', true)->update(['is_active' => false]);

        // 2. Buat periode baru dan langsung aktifkan
        Period::create([
            'name' => $request->new_period_name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'target_mitra_per_user' => 1, // Tetapkan kuota dasar 1 orang 1 mitra
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Periode lama resmi ditutup. Kuartal baru berhasil dibuka!');
    }
}