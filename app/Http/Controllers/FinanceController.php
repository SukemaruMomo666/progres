<?php

namespace App\Http\Controllers;

use App\Models\Finance;
use App\Models\Period;
use App\Models\Project;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    /**
     * Menampilkan Halaman Buku Kas
     */
    public function index()
    {
        if (!auth()->user()->hasRole(['Founder', 'Co-Founder', 'HR'])) {
            abort(403, 'Akses terbatas.');
        }

        $activePeriod = Period::where('is_active', true)->first();

        if (!$activePeriod) {
            return view('finance.index', [
                'transactions' => [], 'income' => 0, 'expense' => 0, 'balance' => 0, 'projects' => [], 'activePeriod' => null
            ]);
        }

        $transactions = Finance::with('project')
            ->where('period_id', $activePeriod->id)
            ->orderBy('transaction_date', 'desc')
            ->get();

        $income = $transactions->where('type', 'Income')->sum('amount');
        $expense = $transactions->where('type', 'Expense')->sum('amount');
        $balance = $income - $expense;

        $projects = Project::where('period_id', $activePeriod->id)->get()->map(function($project) use ($transactions) {
            $total_paid = $transactions->where('project_id', $project->id)->where('type', 'Income')->sum('amount');
            $project->total_paid = $total_paid;
            $project->remaining_payment = $project->total_price - $total_paid;
            $project->payment_percentage = $project->total_price > 0 ? round(($total_paid / $project->total_price) * 100) : 0;
            return $project;
        });

        return view('finance.index', compact('transactions', 'income', 'expense', 'balance', 'projects', 'activePeriod'));
    }

    /**
     * Store (Mencatat Transaksi Baru)
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasRole(['Founder', 'Co-Founder', 'HR'])) abort(403);

        $request->validate([
            'type' => 'required|in:Income,Expense',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string',
            'project_id' => 'nullable|exists:projects,id',
            'transaction_date' => 'required|date',
        ]);

        $activePeriod = Period::where('is_active', true)->firstOrFail();

        DB::transaction(function () use ($request, $activePeriod) {
            $finance = Finance::create([
                'period_id' => $activePeriod->id,
                'project_id' => $request->project_id,
                'type' => $request->type,
                'category' => $request->category,
                'amount' => $request->amount,
                'description' => $request->description,
                'recorded_by' => Auth::id(),
                'transaction_date' => $request->transaction_date,
            ]);

            // Update status proyek jika ada hubungannya
            if ($request->project_id && $request->type === 'Income') {
                $project = Project::find($request->project_id);
                if (str_contains(strtolower($request->category), 'dp')) {
                    $project->update(['payment_status' => 'DP Paid', 'status' => 'In Progress']);
                }
            }

            ActivityLog::record(
                'Keuangan - Tambah', 
                "Mencatat {$request->type} baru: '{$request->category}' sebesar Rp " . number_format($request->amount, 0, ',', '.')
            );
        });

        return redirect()->back()->with('success', 'Transaksi berhasil dicatat.');
    }

    /**
     * Update (Mengoreksi Transaksi yang Salah)
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasRole(['Founder', 'Co-Founder', 'HR'])) abort(403);

        $finance = Finance::findOrFail($id);
        $oldData = "{$finance->category} (Rp " . number_format($finance->amount, 0, ',', '.') . ")";

        $request->validate([
            'amount' => 'required|numeric|min:0',
            'category' => 'required|string',
        ]);

        DB::transaction(function () use ($request, $finance, $oldData) {
            $finance->update([
                'amount' => $request->amount,
                'category' => $request->category,
                'description' => $request->description,
            ]);

            ActivityLog::record(
                'Keuangan - Edit', 
                "Mengubah data transaksi dari {$oldData} menjadi '{$request->category}' (Rp " . number_format($request->amount, 0, ',', '.') . ")."
            );
        });

        return redirect()->back()->with('success', 'Transaksi berhasil diperbarui.');
    }

    /**
     * Destroy (Menghapus Transaksi - Data harus hilang dari buku kas)
     */
    public function destroy($id)
    {
        if (!auth()->user()->hasRole(['Founder', 'Co-Founder', 'HR'])) abort(403);

        $finance = Finance::findOrFail($id);
        $desc = "{$finance->category} (Rp " . number_format($finance->amount, 0, ',', '.') . ")";

        DB::transaction(function () use ($finance, $desc) {
            $finance->delete();

            ActivityLog::record(
                'Keuangan - Hapus', 
                "Menghapus data transaksi keuangan: {$desc} secara permanen."
            );
        });

        return redirect()->back()->with('success', 'Transaksi berhasil dihapus.');
    }
}