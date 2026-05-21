<?php

namespace App\Http\Controllers;

use App\Models\Period;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeriodController extends Controller
{
    /**
     * Menampilkan daftar semua riwayat Periode / Kuartal Kerja.
     */
    public function index()
    {
        // Proteksi: Hanya manajemen
        if (!auth()->user()->hasRole(['Founder', 'Co-Founder', 'HR'])) {
            abort(403, 'Anda tidak memiliki otoritas untuk melihat data Periode.');
        }

        $periods = Period::orderBy('start_date', 'desc')->get();
        
        return view('admin.periods.index', compact('periods'));
    }

    /**
     * Menyimpan data periode baru (jika ditambahkan manual di luar sistem Reset Kuartal).
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasRole(['Founder', 'Co-Founder', 'HR'])) abort(403);

        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'start_date'            => 'required|date',
            'end_date'              => 'required|date|after:start_date',
            'target_mitra_per_user' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($validated) {
            $period = Period::create([
                'name'                  => $validated['name'],
                'start_date'            => $validated['start_date'],
                'end_date'              => $validated['end_date'],
                'target_mitra_per_user' => $validated['target_mitra_per_user'],
                'is_active'             => false, // Default tidak aktif agar tidak menabrak kuartal berjalan
            ]);

            ActivityLog::record(
                'Tambah Periode', 
                "Menambahkan data kuartal kerja baru: {$period->name} ke dalam arsip sistem."
            );
        });

        return back()->with('success', 'Periode baru berhasil ditambahkan ke arsip.');
    }

    /**
     * Memperbarui informasi tanggal atau target KPI pada periode tertentu.
     */
    public function update(Request $request, Period $period)
    {
        if (!auth()->user()->hasRole(['Founder', 'Co-Founder', 'HR'])) abort(403);

        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'start_date'            => 'required|date',
            'end_date'              => 'required|date|after:start_date',
            'target_mitra_per_user' => 'required|integer|min:1',
        ]);

        $oldName = $period->name;

        DB::transaction(function () use ($period, $validated, $oldName) {
            $period->update($validated);

            ActivityLog::record(
                'Edit Periode', 
                "Memperbarui parameter (tanggal / target KPI) pada periode: {$oldName}."
            );
        });

        return back()->with('success', 'Data periode kerja berhasil diperbarui.');
    }

    /**
     * Menghapus riwayat periode (Hanya jika periode tidak sedang aktif).
     */
    public function destroy(Period $period)
    {
        if (!auth()->user()->hasRole(['Founder', 'Co-Founder', 'HR'])) abort(403);

        // Validasi Ekstra: Cegah penghapusan periode yang sedang berjalan
        if ($period->is_active) {
            return back()->withErrors('Akses ditolak! Anda tidak boleh menghapus Kuartal Kerja yang sedang aktif.');
        }

        $periodName = $period->name;

        DB::transaction(function () use ($period, $periodName) {
            $period->delete();

            ActivityLog::record(
                'Hapus Periode', 
                "Menghapus riwayat periode kerja '{$periodName}' secara permanen dari sistem."
            );
        });

        return back()->with('success', 'Riwayat periode berhasil dihapus.');
    }

    // Fungsi bawaan yang tidak digunakan karena memakai Modal
    public function create() { }
    public function show(Period $period) { }
    public function edit(Period $period) { }
}