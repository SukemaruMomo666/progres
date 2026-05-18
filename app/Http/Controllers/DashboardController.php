<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Period;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index()
    {
        $activePeriod = Period::where('is_active', true)->first();

        if (!$activePeriod) {
            return view('dashboard', [
                'projects' => [], 'activePeriod' => null, 'clients' => [], 'team' => []
            ]);
        }

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

        // Ambil data untuk Form Input Modal
        $clients = Client::all();
        $team = User::where('is_active', true)->get();

        return view('dashboard', compact('projects', 'activePeriod', 'clients', 'team'));
    }

    /**
     * LOGIKA DEWA: Menyimpan Proyek Baru Terintegrasi
     */
    public function storeProject(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'client_id' => 'required|exists:clients,id',
            'pic_id' => 'required|exists:users,id',
            'finder_id' => 'required|exists:users,id',
            'total_price' => 'required|numeric|min:0',
            'dp_amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'deadline' => 'required|date|after_or_equal:start_date',
        ]);

        $activePeriod = Period::where('is_active', true)->firstOrFail();

        Project::create([
            'uuid' => (string) Str::uuid(),
            'name' => $request->name,
            'client_id' => $request->client_id,
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

        return redirect()->back()->with('success', 'Proyek baru berhasil diluncurkan!');
    }
}