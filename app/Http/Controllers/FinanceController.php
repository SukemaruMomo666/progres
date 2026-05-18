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
        // 1. Ambil periode yang sedang aktif
        $activePeriod = Period::where('is_active', true)->first();

        if (!$activePeriod) {
            return view('finance.index', [
                'transactions' => [], 'income' => 0, 'expense' => 0, 'balance' => 0, 'projects' => [], 'activePeriod' => null
            ]);
        }

        // 2. Tarik semua transaksi di periode aktif ini
        $transactions = Finance::with('project')
            ->where('period_id', $activePeriod->id)
            ->orderBy('transaction_date', 'desc')
            ->get();

        // 3. Logika Hitung Total (Income, Expense, Net Balance)
        $income = $transactions->where('type', 'Income')->sum('amount');
        $expense = $transactions->where('type', 'Expense')->sum('amount');
        $balance = $income - $expense;

        // 4. Ambil daftar proyek untuk pilihan di form modal (DP/Pelunasan)
        $projects = Project::where('period_id', $activePeriod->id)->get();

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

        // Logika Dewa Otomatis: Jika transaksi ini adalah DP/Pelunasan Proyek, update status bayar proyeknya
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