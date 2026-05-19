<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index()
    {
        // Ambil log beserta data usernya, urutkan dari yang paling gres
        $logs = ActivityLog::with('user')->orderBy('created_at', 'desc')->get();
        
        return view('admin.logs.index', compact('logs'));
    }
}