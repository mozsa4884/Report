<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\DailyReportItem;
use App\Models\Site;
use App\Models\Tank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $stats = [];
        $recentReports = [];
        
        if ($user->isFuelman()) {
            $stats['drafts'] = DailyReport::where('fuelman_id', $user->id)->where('status', 'draft')->count();
            $stats['submitted'] = DailyReport::where('fuelman_id', $user->id)->where('status', 'submitted')->count();
            $stats['verified'] = DailyReport::where('fuelman_id', $user->id)->where('status', 'verified')->count();
            $stats['approved'] = DailyReport::where('fuelman_id', $user->id)->where('status', 'approved')->count();
            $stats['rejected'] = DailyReport::where('fuelman_id', $user->id)->where('status', 'rejected')->count();
            
            $recentReports = DailyReport::where('fuelman_id', $user->id)
                ->orderBy('date', 'desc')
                ->limit(5)
                ->get();
                
            $latestDraft = DailyReport::where('fuelman_id', $user->id)
                ->where('status', 'draft')
                ->orderBy('date', 'desc')
                ->first();
                
            $latestRejected = DailyReport::where('fuelman_id', $user->id)
                ->where('status', 'rejected')
                ->orderBy('date', 'desc')
                ->first();

            return view('dashboard', compact('stats', 'recentReports', 'latestDraft', 'latestRejected'));
        } 
        
        if ($user->isGl()) {
            $stats['pending_verification'] = DailyReport::where('status', 'submitted')->count();
            $stats['verified_by_me'] = DailyReport::where('status', 'verified')->where('gl_id', $user->id)->count();
            $stats['total_approved'] = DailyReport::where('status', 'approved')->count();
            $stats['total_rejected_by_me'] = DailyReport::where('status', 'rejected')->where('gl_id', $user->id)->count();

            $recentReports = DailyReport::orderBy('date', 'desc')
                ->limit(5)
                ->get();

            $pendingReports = DailyReport::where('status', 'submitted')
                ->orderBy('date', 'asc')
                ->get();

            return view('dashboard', compact('stats', 'recentReports', 'pendingReports'));
        }
        
        if ($user->isSpv()) {
            $stats['pending_approval'] = DailyReport::where('status', 'verified')->count();
            $stats['approved_by_me'] = DailyReport::where('status', 'approved')->where('spv_id', $user->id)->count();
            $stats['total_reports'] = DailyReport::count();
            
            $recentReports = DailyReport::orderBy('date', 'desc')
                ->limit(5)
                ->get();

            $pendingReports = DailyReport::where('status', 'verified')
                ->orderBy('date', 'asc')
                ->get();

            // Total Fuel usage (sum of fm_pakai for all reports including draft)
            $totalUsage = DailyReportItem::sum('fm_pakai');

            // Latest approved report for fuel status widget
            $latestApprovedReport = DailyReport::where('status', 'approved')
                ->orderBy('date', 'desc')
                ->first();

            // Sounding status for active tanks (TODAY'S report only)
            $today = date('Y-m-d');
            $todayReport = DailyReport::whereDate('date', $today)
                ->orderBy('created_at', 'desc')
                ->first();

            $tankStatus = [];
            if ($todayReport) {
                $tankStatus = DailyReportItem::with('tank')
                    ->where('daily_report_id', $todayReport->id)
                    ->get();
            }

            return view('dashboard', compact('stats', 'recentReports', 'pendingReports', 'totalUsage', 'tankStatus', 'todayReport', 'latestApprovedReport'));
        }

        abort(403);
    }

    public function analytics(Request $request)
    {
        if (Auth::user()->isFuelman()) {
            abort(403, 'Fuelman tidak memiliki akses ke rekap dan analisis BBM.');
        }

        // Get all active sites for dropdown
        $sites = Site::where('is_active', true)->orderBy('name')->get();
        
        // Get selected filters from request
        $siteId = $request->get('site_id');
        $month = $request->get('month');
        $year = $request->get('year');
        
        // Generate year options (last 5 years to next year)
        $currentYear = (int) date('Y');
        $years = range($currentYear - 5, $currentYear + 1);

        // If no filters selected, return empty data
        if (!$siteId || !$month || !$year) {
            return view('reports.analytics', [
                'approvedReports' => collect([]),
                'usageData' => collect([]),
                'sites' => $sites,
                'siteId' => $siteId,
                'month' => $month,
                'year' => $year,
                'years' => $years,
                'summaryStats' => null,
                'previousMonthComparison' => null,
            ]);
        }

        // Build query for approved reports with filters
        $approvedReportsQuery = DailyReport::where('status', 'approved')
            ->where('site_id', $siteId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month);
        
        $approvedReports = $approvedReportsQuery->orderBy('date', 'desc')->get();
        
        // Build query for usage data with filters
        $usageDataQuery = DailyReportItem::whereHas('dailyReport', function($q) use ($siteId, $month, $year) {
                $q->where('status', 'approved')
                  ->where('site_id', $siteId)
                  ->whereYear('date', $year)
                  ->whereMonth('date', $month);
            })
            ->select(
                'tank_id', 
                DB::raw('SUM(fm_pakai) as total_pakai'),
                DB::raw('AVG(fm_pakai) as avg_pakai'),
                DB::raw('MAX(fm_pakai) as max_pakai'),
                DB::raw('COUNT(*) as report_count')
            )
            ->groupBy('tank_id')
            ->with('tank');
        
        $usageData = $usageDataQuery->get();

        // Calculate summary statistics
        $totalUsage = $usageData->sum('total_pakai');
        $totalReports = $approvedReports->count();
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $avgDailyUsage = $totalReports > 0 ? $totalUsage / $totalReports : 0;
        
        $summaryStats = [
            'total_usage' => $totalUsage,
            'total_reports' => $totalReports,
            'avg_daily_usage' => $avgDailyUsage,
            'days_in_month' => $daysInMonth,
            'tank_count' => $usageData->count(),
        ];

        // Get previous month data for comparison
        $prevMonth = $month - 1;
        $prevYear = $year;
        if ($prevMonth < 1) {
            $prevMonth = 12;
            $prevYear = $year - 1;
        }

        $prevMonthUsage = DailyReportItem::whereHas('dailyReport', function($q) use ($siteId, $prevMonth, $prevYear) {
                $q->where('status', 'approved')
                  ->where('site_id', $siteId)
                  ->whereYear('date', $prevYear)
                  ->whereMonth('date', $prevMonth);
            })
            ->sum('fm_pakai');

        $previousMonthComparison = null;
        if ($prevMonthUsage > 0) {
            $difference = $totalUsage - $prevMonthUsage;
            $percentageChange = ($difference / $prevMonthUsage) * 100;
            $previousMonthComparison = [
                'prev_usage' => $prevMonthUsage,
                'difference' => $difference,
                'percentage' => $percentageChange,
                'trend' => $difference > 0 ? 'up' : ($difference < 0 ? 'down' : 'stable'),
            ];
        }

        return view('reports.analytics', compact(
            'approvedReports', 
            'usageData', 
            'sites', 
            'siteId', 
            'month', 
            'year', 
            'years',
            'summaryStats',
            'previousMonthComparison'
        ));
    }
}
