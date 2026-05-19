<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Period;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

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

        $clients = Client::all();
        $team = User::where('is_active', true)->get();

        return view('dashboard', compact('projects', 'activePeriod', 'clients', 'team'));
    }

    public function storeProject(Request $request)
    {
        // Validasi
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

        // Gunakan Transaction agar jika error, data Klien tidak terbuat sia-sia
        DB::transaction(function () use ($request, $activePeriod) {
            
            $client = Client::firstOrCreate(
                ['name' => $request->client_name],
                ['phone' => $request->client_phone]
            );

            Project::create([
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
            
        });

        return redirect()->back()->with('success', 'Proyek dan data klien baru berhasil disimpan!');
    }
}