<?php

namespace App\Http\Controllers;

use App\Models\Finance;
use App\Models\Period;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinanceController extends Controller
{
    public function index()
    {
        $activePeriod = Period::where('is_active', true)->first();

        if (!$activePeriod) {
            return view('finance.index', [
                'transactions' => [], 'income' => 0, 'expense' => 0, 'balance' => 0, 'projects' => [], 'activePeriod' => null
            ]);
        }

        // 1. Ambil semua riwayat transaksi kas
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

            // Auto-update status Lunas di memori jika bayaran sudah 100% atau lebih
            if ($project->payment_percentage >= 100) {
                $project->payment_status = 'Fully Paid';
            }

            return $project;
        });

        return view('finance.index', compact('transactions', 'income', 'expense', 'balance', 'projects', 'activePeriod'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:Income,Expense',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string',
            'project_id' => 'nullable|exists:projects,id',
            'transaction_date' => 'required|date',
        ]);

        $activePeriod = Period::where('is_active', true)->firstOrFail();

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

        // Auto Update Database Status Proyek Jika Lunas/DP
        if ($request->project_id && $request->type === 'Income') {
            $project = Project::find($request->project_id);
            if (str_contains(strtolower($request->category), 'dp')) {
                $project->update(['payment_status' => 'DP Paid', 'status' => 'In Progress']);
            } elseif (str_contains(strtolower($request->category), 'lunas') || str_contains(strtolower($request->category), 'pelunasan')) {
                $project->update(['payment_status' => 'Fully Paid']);
            }
        }

        return redirect()->back()->with('success', 'Transaksi keuangan berhasil dicatat!');
    }
}