<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
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
        if ($user->role === UserRole::Admin || collect($user->permissions)->contains('clients')) {
            $stats['clients_count'] = Client::count();
        }

        if ($user->role === UserRole::Admin) {
            $stats['tickets_count'] = Ticket::where('status', 'new')->count();
            $stats['applications_pending'] = Application::where('status', 'pending')->count();
        } else {
            $stats['tickets_count'] = Ticket::where('staff_id', $user->id)
                ->where('status', 'new')
                ->count();
        }

        // 2. Линейный график (Динамика за 30 дней)
        // Раньше группировка и форматирование шли через to_char() —
        // функцию, которой в SQLite не существует вообще (тесты это и
        // поймали), а на MySQL (если в проде именно он) её тоже нет —
        // это функция специфична для PostgreSQL. Группировка и
        // форматирование даты перенесены в PHP через Carbon — работает
        // одинаково на любой СУБД.
        $recentTickets = Ticket::where('created_at', '>=', Carbon::now()->subDays(30))->get();

        $chartData = $recentTickets
            ->groupBy(fn ($ticket) => $ticket->created_at->format('d.m'))
            ->map(fn ($group, $date) => [
                'date' => $date,
                'count' => $group->count(),
                'sortKey' => $group->first()->created_at,
            ])
            ->sortBy('sortKey')
            ->values()
            ->map(fn ($item) => ['date' => $item['date'], 'count' => $item['count']]);

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