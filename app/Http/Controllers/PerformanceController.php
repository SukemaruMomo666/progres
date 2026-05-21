<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Period;
use App\Models\ActivityLog; // <-- Wajib untuk Audit Log
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // <-- Wajib untuk Keamanan Database

class PerformanceController extends Controller
{
    public function index()
    {
        // Proteksi Otoritas: Hanya Role Founder, Co-Founder, dan HR
        if (!auth()->user()->hasRole(['Founder', 'Co-Founder', 'HR'])) {
            abort(403, 'Anda tidak memiliki otoritas untuk melihat data KPI Tim.');
        }

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
        // Proteksi Otoritas Ekstra Ketat
        if (!auth()->user()->hasRole(['Founder', 'Co-Founder', 'HR'])) {
            abort(403, 'Anda tidak memiliki otoritas untuk mereset kuartal kerja.');
        }

        $request->validate([
            'new_period_name' => 'required|string|max:255',
            'start_date'      => 'required|date',
            'end_date'        => 'required|date|after:start_date',
        ]);

        // Mengamankan proses penggantian kuartal
        DB::transaction(function () use ($request) {
            
            // Cari tahu nama periode lama sebelum dinonaktifkan (untuk laporan log)
            $oldPeriod = Period::where('is_active', true)->first();
            $oldPeriodName = $oldPeriod ? $oldPeriod->name : 'Sistem Kosong/Awal';

            // 1. Nonaktifkan semua periode yang sedang berjalan
            if ($oldPeriod) {
                $oldPeriod->update(['is_active' => false]);
            }

            // 2. Buat periode baru dan langsung aktifkan
            Period::create([
                'name'                  => $request->new_period_name,
                'start_date'            => $request->start_date,
                'end_date'              => $request->end_date,
                'target_mitra_per_user' => 1, // Tetapkan kuota dasar 1 orang 1 mitra
                'is_active'             => true,
            ]);

            // 3. JEJAK DIGITAL: Catat momentum perpindahan kuartal
            ActivityLog::record(
                'Reset Kuartal Kerja', 
                "Menutup periode lama '{$oldPeriodName}' dan resmi membuka operasional kuartal baru: '{$request->new_period_name}'."
            );
        });

        return redirect()->back()->with('success', 'Periode lama resmi ditutup. Kuartal baru berhasil dibuka dan tercatat di Log!');
    }
}