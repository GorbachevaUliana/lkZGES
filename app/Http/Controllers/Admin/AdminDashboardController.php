<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Client;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $stats = [];

        // 1. Общая статистика
        if ($user->role === 'admin' || collect($user->permissions)->contains('clients')) {
            $stats['clients_count'] = Client::count();
        }

        if ($user->role === 'admin') {
            $stats['tickets_count'] = Ticket::where('status', 'new')->count();
            $stats['applications_pending'] = Application::where('status', 'pending')->count();
        } else {
            $stats['tickets_count'] = Ticket::where('staff_id', $user->id)
                ->where('status', 'new')
                ->count();
        }

        // 2. Линейный график (Динамика за 30 дней)
        $chartData = Ticket::where('created_at', '>=', Carbon::now()->subDays(30))
            ->select(
                DB::raw("to_char(created_at, 'DD.MM') as date"),
                DB::raw('count(*) as count')
            )
            ->groupBy('date')
            ->orderBy(DB::raw('min(created_at)'), 'ASC')
            ->get();

        // 3. Круговая диаграмма (Темы)
        $pieData = Ticket::select('subject as name', DB::raw('count(*) as value'))
            ->groupBy('subject')
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'chartData' => $chartData,
            'pieData' => $pieData,
        ]);
    }
}
