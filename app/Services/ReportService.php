<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ReportService
{
    public function projectProfitability(): array
    {
        return Cache::driver(config('api.cache.driver', 'file'))
            ->remember('report:project_profitability', config('api.cache.ttl', 300), function () {
                return DB::table('projects as p')
                    ->join('clients as c', 'c.id', '=', 'p.client_id')
                    ->leftJoin('invoices as i', 'i.project_id', '=', 'p.id')
                    ->leftJoin('tasks as t', 't.project_id', '=', 'p.id')
                    ->leftJoin('time_entries as te', 'te.task_id', '=', 't.id')
                    ->select([
                        'p.id',
                        'p.name as project',
                        'c.name as client',
                        'p.budget',
                        'p.status',
                        DB::raw('COALESCE(SUM(DISTINCT i.total), 0) as total_invoiced'),
                        DB::raw('COALESCE(SUM(te.hours), 0) as total_hours'),
                        DB::raw('COALESCE(SUM(te.hours * te.hourly_rate), 0) as total_cost'),
                    ])
                    ->groupBy('p.id', 'p.name', 'c.name', 'p.budget', 'p.status')
                    ->get()
                    ->map(function ($row) {
                        $row->profit = $row->total_invoiced - $row->total_cost;
                        $row->profit_margin = $row->total_invoiced > 0 ? round(($row->profit / $row->total_invoiced) * 100, 2) : 0;
                        return $row;
                    })
                    ->sortByDesc('profit')
                    ->values()
                    ->toArray();
            });
    }

    public function teamProductivity(): array
    {
        return Cache::driver(config('api.cache.driver', 'file'))
            ->remember('report:team_productivity', config('api.cache.ttl', 300), function () {
                return DB::table('users as u')
                    ->leftJoin('time_entries as te', 'te.user_id', '=', 'u.id')
                    ->leftJoin('tasks as t', 't.assignee_id', '=', 'u.id')
                    ->select([
                        'u.id',
                        'u.name',
                        DB::raw('COALESCE(SUM(te.hours), 0) as total_hours_logged'),
                        DB::raw('COALESCE(SUM(te.hours * te.hourly_rate), 0) as total_billable'),
                        DB::raw('COUNT(DISTINCT t.id) as total_tasks'),
                        DB::raw('SUM(CASE WHEN t.status = "completed" THEN 1 ELSE 0 END) as completed_tasks'),
                    ])
                    ->groupBy('u.id', 'u.name')
                    ->orderByDesc('total_hours_logged')
                    ->get()->toArray();
            });
    }

    public function revenue(): array
    {
        return Cache::driver(config('api.cache.driver', 'file'))
            ->remember('report:revenue', config('api.cache.ttl', 300), function () {
                return DB::table('invoices')
                    ->select([
                        DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                        DB::raw('SUM(total) as total_revenue'),
                        DB::raw('SUM(CASE WHEN status = "paid" THEN total ELSE 0 END) as collected'),
                        DB::raw('COUNT(*) as invoice_count'),
                    ])
                    ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m')")
                    ->orderBy('month', 'desc')
                    ->limit(12)
                    ->get()->toArray();
            });
    }

    public function cashFlow(): array
    {
        return Cache::driver(config('api.cache.driver', 'file'))
            ->remember('report:cashflow', config('api.cache.ttl', 300), function () {
                $inflow = DB::table('payments')
                    ->whereNull('refunded_at')
                    ->select([
                        DB::raw("DATE_FORMAT(paid_at, '%Y-%m') as month"),
                        DB::raw('SUM(amount) as inflow'),
                    ])
                    ->groupByRaw("DATE_FORMAT(paid_at, '%Y-%m')")
                    ->get()->keyBy('month');

                return ['cash_flow' => $inflow->values()->toArray()];
            });
    }

    public function dashboard(): array
    {
        return Cache::driver(config('api.cache.driver', 'file'))
            ->remember('report:dashboard', 120, function () {
                return [
                    'total_clients'   => DB::table('clients')->where('is_active', true)->count(),
                    'total_projects'  => DB::table('projects')->count(),
                    'active_projects' => DB::table('projects')->where('status', 'active')->count(),
                    'total_tasks'     => DB::table('tasks')->count(),
                    'open_tasks'      => DB::table('tasks')->whereIn('status', ['pending', 'in_progress'])->count(),
                    'total_invoiced'  => DB::table('invoices')->sum('total'),
                    'total_collected' => DB::table('payments')->whereNull('refunded_at')->sum('amount'),
                    'overdue_invoices'=> DB::table('invoices')->where('status', 'sent')->where('due_date', '<', now())->count(),
                ];
            });
    }
}
