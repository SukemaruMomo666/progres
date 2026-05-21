<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Client;
use App\Models\ActivityLog;
use App\Models\Period;
use App\Models\Finance;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    /**
     * Menampilkan Halaman Utama Dashboard
     */
    public function index() 
    { 
        return view('dashboard'); 
    }

    /**
     * Menginisiasi dan Menyimpan Proyek Baru (Hanya Admin)
     */
    public function store(Request $request)
    {
        // Validasi input operasional proyek dengan proteksi tanggal logis
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'client_name'   => 'required|string|max:255',
            'client_phone'  => 'nullable|string|max:20',
            'pic_id'        => 'required|exists:users,id',
            'finder_id'     => 'required|exists:users,id',
            'total_price'   => 'required|numeric|min:0',
            'dp_amount'     => 'required|numeric|min:0',
            'start_date'    => 'required|date',
            'deadline'      => 'required|date|after_or_equal:start_date', // Mencegah deadline minus
        ]);

        DB::transaction(function () use ($validated) {
            // 1. Buat Klien Baru (Atau update jika sudah ada)
            $client = Client::firstOrCreate(
                ['name'  => $validated['client_name']],
                ['phone' => $validated['client_phone']]
            );

            // 2. Ambil periode aktif kuartal kerja
            $period = Period::where('is_active', true)->firstOrFail();

            // 3. Simpan Proyek Baru
            $project = Project::create([
                'uuid'           => Str::uuid(),
                'name'           => $validated['name'],
                'client_id'      => $client->id,
                'period_id'      => $period->id,
                'pic_id'         => $validated['pic_id'],
                'finder_id'      => $validated['finder_id'],
                'total_price'    => $validated['total_price'],
                'dp_amount'      => $validated['dp_amount'],
                'payment_status' => ($validated['dp_amount'] > 0) ? 'DP Paid' : 'Unpaid',
                'start_date'     => $validated['start_date'],
                'deadline'       => $validated['deadline'],
                'status'         => 'Planning',
            ]);

            // 4. Otomatis Catat DP ke Buku Kas Keuangan jika nominal > 0
            if ($validated['dp_amount'] > 0) {
                Finance::create([
                    'period_id'        => $period->id,
                    'project_id'       => $project->id,
                    'amount'           => $validated['dp_amount'],
                    'type'             => 'Income',
                    'category'         => 'DP Awal Proyek',
                    'description'      => "DP Awal untuk proyek: " . $validated['name'],
                    'recorded_by'      => auth()->id(),
                    'transaction_date' => now(),
                ]);
            }

            // 5. JEJAK DIGITAL: Catat Log Inisiasi Proyek
            ActivityLog::record(
                'Inisiasi Proyek', 
                "Berhasil meresmikan proyek baru '{$project->name}' untuk klien '{$validated['client_name']}' dengan DP awal Rp " . number_format($validated['dp_amount'], 0, ',', '.')
            );
        });

        return redirect()->route('dashboard')->with('success', 'Proyek berhasil diinisiasi.');
    }

    /**
     * Menampilkan Detail Spesifik Papan Kanban Proyek
     */
    public function show(Project $project) 
    { 
        return view('projects.show', compact('project')); 
    }

    /**
     * Memperbarui Parameter Operasional Proyek Aktif (Hanya Admin)
     */
    public function update(Request $request, $id)
    {
        // Proteksi Otoritas: Hanya Role Founder, Co-Founder, dan HR yang boleh mengedit
        if (!auth()->user()->hasRole(['Founder', 'Co-Founder', 'HR'])) {
            abort(403, 'Anda tidak memiliki otoritas untuk mengedit data proyek.');
        }

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'client_name'   => 'required|string|max:255',
            'client_phone'  => 'nullable|string|max:20',
            'pic_id'        => 'required|exists:users,id',
            'finder_id'     => 'required|exists:users,id',
            'total_price'   => 'required|numeric|min:0',
            'start_date'    => 'required|date',
            'deadline'      => 'required|date|after_or_equal:start_date',
        ]);

        $project = Project::findOrFail($id);

        DB::transaction(function () use ($project, $validated) {
            // 1. Update Data Klien yang berelasi
            if ($project->client) {
                $project->client->update([
                    'name'  => $validated['client_name'],
                    'phone' => $validated['client_phone'],
                ]);
            }

            // 2. Update Detail Data Proyek (Tanpa menyentuh DP awal)
            $project->update([
                'name'        => $validated['name'],
                'pic_id'      => $validated['pic_id'],
                'finder_id'   => $validated['finder_id'],
                'total_price' => $validated['total_price'],
                'start_date'  => $validated['start_date'],
                'deadline'    => $validated['deadline'],
            ]);

            // 3. JEJAK DIGITAL: Catat Log Pembaruan Detail Proyek
            ActivityLog::record(
                'Pembaruan Proyek', 
                "Mengubah detail parameter operasional pada proyek '{$project->name}'."
            );
        });

        return redirect()->route('dashboard')->with('success', 'Detail operasional proyek berhasil diperbarui.');
    }

    /**
     * Menghapus Proyek dan Sinkronisasi Kas Keuangan (Hanya Admin)
     */
    public function destroy($id)
    {
        // Proteksi Otoritas: Hanya Role Founder, Co-Founder, dan HR yang boleh menghapus
        if (!auth()->user()->hasRole(['Founder', 'Co-Founder', 'HR'])) {
            abort(403, 'Anda tidak memiliki otoritas untuk menghapus data proyek.');
        }

        $project = Project::findOrFail($id);
        
        DB::transaction(function () use ($project) {
            // 1. JEJAK DIGITAL: Catat Log Penghapusan SEBELUM data dihapus dari database
            ActivityLog::record(
                'Penghapusan Proyek', 
                "Menghapus proyek '{$project->name}' secara permanen dan membatalkan seluruh transaksi keuangan terkait dari Buku Kas."
            );

            // 2. Hapus bersih data riwayat keuangan terkait agar pembukuan kas kembali seimbang
            // Perbaikan logika dari if ($project->finances()) menjadi perintah eksekusi langsung
            $project->finances()->delete();

            // 3. Hapus proyek utama (Mendukung SoftDeletes)
            $project->delete();
        });

        return redirect()->route('dashboard')->with('success', 'Proyek beserta seluruh riwayat keuangan terkait berhasil dihapus dari sistem.');
    }
}