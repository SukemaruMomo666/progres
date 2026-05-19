<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Period;
use App\Models\Client;
use App\Models\User;
use App\Models\Finance; // Wajib import model Keuangan
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // Wajib untuk merekam pembuat transaksi
use Illuminate\Support\Facades\Log;  // Wajib untuk merekam error sistem di background

class DashboardController extends Controller
{
    /**
     * Menampilkan Halaman Utama Dashboard
     */
    public function index()
    {
        // 1. Ambil periode kerja yang sedang aktif
        $activePeriod = Period::where('is_active', true)->first();

        if (!$activePeriod) {
            return view('dashboard', [
                'projects' => [], 'activePeriod' => null, 'clients' => [], 'team' => []
            ]);
        }

        // 2. Ambil proyek berjalan di periode aktif beserta hitungan progres tugasnya
        $projects = Project::with(['pic', 'client', 'tasks'])
            ->where('period_id', $activePeriod->id)
            ->get()
            ->map(function ($project) {
                $totalTasks = $project->tasks->count();
                $completedTasks = $project->tasks->where('status', 'Done')->count();
                
                $project->progress = $totalTasks > 0 
                    ? round(($completedTasks / $totalTasks) * 100) 
                    : 0;

                return $project;
            });

        // 3. Ambil data master relasi untuk keperluan input Modal Proyek Baru
        $clients = Client::all();
        $team = User::where('is_active', true)->get();

        return view('dashboard', compact('projects', 'activePeriod', 'clients', 'team'));
    }

    /**
     * LOGIKA DEWA (1000% PERFECT): 
     * Simpan Proyek, Klien Otomatis, & Catat Kas dalam satu eksekusi tahan banting!
     */
    public function storeProject(Request $request)
    {
        // 1. Validasi Ketat
        $request->validate([
            'name' => 'required|string|max:255',
            'client_name' => 'required|string|max:255', 
            'client_phone' => 'nullable|string|max:20',  
            'pic_id' => 'required|exists:users,id',
            'finder_id' => 'required|exists:users,id',
            'total_price' => 'required|numeric|min:0',
            'dp_amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'deadline' => 'required|date|after_or_equal:start_date',
        ]);

        $activePeriod = Period::where('is_active', true)->firstOrFail();

        // 2. Eksekusi Aman (Try-Catch + DB Transaction)
        try {
            DB::transaction(function () use ($request, $activePeriod) {
                
                // A. Cek Klien (Buat baru jika belum pernah ada)
                $client = Client::firstOrCreate(
                    ['name' => $request->client_name],
                    ['phone' => $request->client_phone]
                );

                // B. Buat Proyek Utama
                $project = Project::create([
                    'uuid' => (string) Str::uuid(),
                    'name' => $request->name,
                    'client_id' => $client->id, 
                    'period_id' => $activePeriod->id,
                    'pic_id' => $request->pic_id,
                    'finder_id' => $request->finder_id,
                    'total_price' => $request->total_price,
                    'dp_amount' => $request->dp_amount,
                    'payment_status' => $request->dp_amount > 0 ? 'DP Paid' : 'Unpaid',
                    'start_date' => $request->start_date,
                    'deadline' => $request->deadline,
                    'status' => 'Planning',
                ]);

// ... (kode pembuatan Project di atasnya) ...

                // C. OTOMATISASI BUKU KAS:
                // Jika input Nominal DP lebih dari 0, langsung jadikan Pemasukan!
                if ($request->dp_amount > 0) {
                    Finance::create([
                        'period_id' => $activePeriod->id,
                        'project_id' => $project->id, 
                        'type' => 'Income',
                        'category' => 'DP Proyek: ' . $project->name,
                        'amount' => $request->dp_amount,
                        'description' => 'Pemasukan otomatis dari pembayaran awal (DP) proyek.',
                        'recorded_by' => Auth::id(), 
                        'transaction_date' => now(), 
                    ]);
                }
            });

            return redirect()->back()->with('success', 'Sempurna! Proyek berjalan dan DP otomatis masuk ke Buku Kas.');

        } catch (\Exception $e) {
            // Jika gagal, sistem tidak akan crash, melainkan mencatat masalahnya di Log
            Log::error('Gagal inisiasi proyek XGrow: ' . $e->getMessage());
            
            // Dan mengembalikan pesan peringatan ke UI dengan rapi
            return redirect()->back()->withErrors('Gagal menyimpan proyek. Terjadi kesalahan pada sistem database.');
        }
    }
}