<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        // Proteksi: Hanya manajemen elit
        if (!auth()->user()->hasRole(['Founder', 'Co-Founder', 'HR'])) {
            abort(403, 'Akses ditolak.');
        }

        // Mulai Query dengan relasi user
        $query = ActivityLog::with('user');

        // Filter berdasarkan kata kunci aktivitas (misal: "Hapus")
        if ($request->has('activity') && $request->activity != '') {
            $query->where('activity', 'like', '%' . $request->activity . '%');
        }

        // Filter berdasarkan tanggal
        if ($request->has('date') && $request->date != '') {
            $query->whereDate('created_at', $request->date);
        }

        // Gunakan paginate() agar performa website tidak drop saat log menumpuk
        $logs = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        
        return view('admin.logs.index', compact('logs'));
    }
}