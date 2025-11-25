<?php

namespace App\Http\Controllers;

use App\Http\Resources\DashboardResource;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getStatistic()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        $totalTickets = Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])->count();


        $activeTickets = Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])->where('status', '!=', 'reoslved')->count();

        $resolvedTickets = Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])->where('status', 'resolved')->count();

        $avgResponseTime = Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])->where('status', 'resolved')->whereNotNull('completed_at')->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR,created_at,completed_at)) as avg_response_time'))->value('avg_response_time') ?? 0;

        $statusDistribution = [
            'open' => Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])->where('status', 'open')->count(),
            'onprogress' => Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])->where('status', 'onprogress')->count(),
            'resolved' => Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])->where('status', 'resolved')->count(),
            'rejected' => Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])->where('status', 'rejected')->count()
        ];

        $dashboardData = [
            'total_tickets' => $totalTickets,
            'active_tickets' => $activeTickets,
            'resolved_tickets' => $resolvedTickets,
            'avg_response_time' => round($avgResponseTime, 1),
            'status_distribution' => $statusDistribution
        ];

        return response()->json([
            'message' => 'Data dashboard berhasil diambil',
            'data' => new DashboardResource($dashboardData)
        ]);
    }
}
