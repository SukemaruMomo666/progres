<?php

namespace App\Http\Controllers;

use App\Models\Finance;
use App\Models\Period;
use App\Models\Project;
use App\Models\ActivityLog; // <-- Wajib dipanggil untuk Jejak Digital
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // <-- Wajib dipanggil untuk Keamanan Transaksi

class FinanceController extends Controller
{
    /**
     * Menampilkan Halaman Buku Kas (Hanya Manajemen)
     */
    public function index()
    {
        // Proteksi Otoritas: Hanya Role Founder, Co-Founder, dan HR
        if (!auth()->user()->hasRole(['Founder', 'Co-Founder', 'HR'])) {
            abort(403, 'Anda tidak memiliki otoritas untuk melihat Buku Kas.');
        }

        $activePeriod = Period::where('is_active', true)->first();

        if (!$activePeriod) {
            return view('finance.index', [
                'transactions' => [], 'income' => 0, 'expense' => 0, 'balance' => 0, 'projects' => [], 'activePeriod' => null
            ]);
        }

        // 1. Ambil semua riwayat transaksi kas di kuartal aktif
        $transactions = Finance::with('project')
            ->where('period_id', $activePeriod->id)
            ->orderBy('transaction_date', 'desc')
            ->get();

        $income = $transactions->where('type', 'Income')->sum('amount');
        $expense = $transactions->where('type', 'Expense')->sum('amount');
        $balance = $income - $expense;

        // 2. LOGIKA DEWA: Hitung Persentase Pembayaran (DP vs Total) untuk setiap Proyek
        $projects = Project::where('period_id', $activePeriod->id)->get()->map(function($project) use ($transactions) {
            // Jumlahkan semua uang Pemasukan (DP/Cicilan/Lunas) yang masuk atas nama proyek ini
            $total_paid = $transactions->where('project_id', $project->id)->where('type', 'Income')->sum('amount');
            
            $project->total_paid = $total_paid;
            $project->remaining_payment = $project->total_price - $total_paid; // Sisa tagihan
            
            // Hitung persentase (Hindari pembagian dengan 0)
            $project->payment_percentage = $project->total_price > 0 
                ? round(($total_paid / $project->total_price) * 100) 
                : 0;

            // Auto-update status Lunas di memori halaman jika bayaran sudah 100% atau lebih
            if ($project->payment_percentage >= 100) {
                $project->payment_status = 'Fully Paid';
            }

            return $project;
        });

        return view('finance.index', compact('transactions', 'income', 'expense', 'balance', 'projects', 'activePeriod'));
    }

    /**
     * Mencatat Transaksi Keuangan Baru (Income/Expense)
     */
    public function store(Request $request)
    {
        // Proteksi Otoritas: Hanya Role Founder, Co-Founder, dan HR
        if (!auth()->user()->hasRole(['Founder', 'Co-Founder', 'HR'])) {
            abort(403, 'Anda tidak memiliki otoritas untuk mencatat transaksi keuangan.');
        }

        $request->validate([
            'type' => 'required|in:Income,Expense',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string',
            'project_id' => 'nullable|exists:projects,id',
            'transaction_date' => 'required|date',
        ]);

        $activePeriod = Period::where('is_active', true)->firstOrFail();

        // Menggunakan Database Transaction agar sinkronisasi kas dan status proyek 100% aman
        DB::transaction(function () use ($request, $activePeriod) {
            
            // 1. Buat Catatan Keuangan
            Finance::create([
                'period_id' => $activePeriod->id,
                'project_id' => $request->project_id,
                'type' => $request->type,
                'category' => $request->category,
                'amount' => $request->amount,
                'description' => $request->description,
                'recorded_by' => Auth::id(),
                'transaction_date' => $request->transaction_date,
            ]);

            // 2. Auto Update Database Status Proyek Jika Lunas/DP
            if ($request->project_id && $request->type === 'Income') {
                $project = Project::find($request->project_id);
                if (str_contains(strtolower($request->category), 'dp')) {
                    $project->update(['payment_status' => 'DP Paid', 'status' => 'In Progress']);
                } elseif (str_contains(strtolower($request->category), 'lunas') || str_contains(strtolower($request->category), 'pelunasan')) {
                    $project->update(['payment_status' => 'Fully Paid']);
                }
            }

            // 3. JEJAK DIGITAL: Catat ke Audit Log
            $tipeKas = $request->type === 'Income' ? 'Pemasukan' : 'Pengeluaran';
            $nominal = number_format($request->amount, 0, ',', '.');
            
            ActivityLog::record(
                'Transaksi Buku Kas', 
                "Mencatat {$tipeKas} sebesar Rp {$nominal} untuk kategori '{$request->category}'."
            );
        });

        return redirect()->back()->with('success', 'Transaksi keuangan berhasil dicatat & masuk ke Audit Logs!');
    }
}