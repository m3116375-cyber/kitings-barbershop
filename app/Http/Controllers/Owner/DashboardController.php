<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, ReportService $reports)
    {
        $from = $this->date($request->query('from'), now()->startOfMonth()->toDateString());
        $to = $this->date($request->query('to'), now()->endOfMonth()->toDateString());
        if ($to < $from) {
            [$from, $to] = [$to, $from];
        }

        $summary = $reports->summary($reports->bookings($from, $to));

        return view('owner.dashboard', [
            'from' => $from,
            'to' => $to,
            'summary' => $summary,
            'topServices' => $reports->topServices($from, $to),
            'topProducts' => $reports->topProducts($from, $to),
            'weekly' => $reports->weeklyRevenue(8),
            'occupancy' => $reports->occupancyByDay($from, $to),
        ]);
    }

    private function date(?string $value, string $default): string
    {
        return ($value && Carbon::hasFormat($value, 'Y-m-d')) ? $value : $default;
    }
}
