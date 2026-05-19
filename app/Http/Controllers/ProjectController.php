<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi input sesuai dengan kolom yang ada di database
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'client_name'   => 'required|string|max:255',
            'client_phone'  => 'nullable|string|max:20',
            'pic_id'        => 'required|exists:users,id',
            'finder_id'     => 'required|exists:users,id',
            'total_price'   => 'required|numeric',
            'dp_amount'     => 'required|numeric',
            'start_date'    => 'required|date',
            'deadline'      => 'required|date',
        ]);

        DB::transaction(function () use ($validated) {
            // 1. Buat Klien Baru
            $client = Client::create([
                'name'  => $validated['client_name'],
                'phone' => $validated['client_phone'],
            ]);

            // 2. Ambil periode aktif
            $period = \App\Models\Period::where('is_active', true)->firstOrFail();

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

            // 4. Otomatis Catat DP ke Keuangan jika nominal DP lebih besar dari 0
            if ($validated['dp_amount'] > 0) {
                \App\Models\Finance::create([
                    'project_id'  => $project->id,
                    'amount'      => $validated['dp_amount'],
                    'type'        => 'Income',
                    'notes'       => "DP Awal untuk proyek: " . $validated['name'],
                    'recorded_by' => auth()->id(),
                ]);
            }
        });

        return redirect()->route('dashboard')->with('success', 'Proyek berhasil diinisiasi.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Proteksi Otoritas: Hanya Role Founder, Co-Founder, dan HR yang boleh menghapus
        if (!auth()->user()->hasRole(['Founder', 'Co-Founder', 'HR'])) {
            abort(403, 'Anda tidak memiliki otoritas untuk menghapus data proyek.');
        }

        // Mencari proyek berdasarkan ID
        $project = Project::findOrFail($id);
        
        DB::transaction(function () use ($project) {
            // PERBAIKAN: Hapus total semua data keuangan yang terikat dengan proyek ini 
            // sehingga pembukuan di page finance ikut bersih tanpa perlu cek kolom 'status'
            if ($project->finances()) {
                $project->finances()->delete();
            }

            // Hapus proyek utama (SoftDelete bekerja jika model menggunakan SoftDeletes)
            $project->delete();
        });

        return redirect()->route('dashboard')->with('success', 'Proyek beserta seluruh riwayat keuangan terkait berhasil dihapus dari sistem.');
    }

    public function index() 
    { 
        return view('dashboard'); 
    }

    public function show(Project $project) 
    { 
        return view('projects.show', compact('project')); 
    }
}