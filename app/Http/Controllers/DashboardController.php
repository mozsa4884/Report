<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\DailyReportItem;
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

            // Total Fuel usage (sum of fm_pakai for the past 30 days)
            $totalUsage = DailyReportItem::whereHas('dailyReport', function($q) {
                    $q->where('status', 'approved');
                })
                ->sum('fm_pakai');

            // Sounding status for active tanks (latest approved report values)
            $latestApprovedReport = DailyReport::where('status', 'approved')
                ->orderBy('date', 'desc')
                ->first();

            $tankStatus = [];
            if ($latestApprovedReport) {
                $tankStatus = DailyReportItem::with('tank')
                    ->where('daily_report_id', $latestApprovedReport->id)
                    ->get();
            }

            return view('dashboard', compact('stats', 'recentReports', 'pendingReports', 'totalUsage', 'tankStatus', 'latestApprovedReport'));
        }

        abort(403);
    }

    public function analytics()
    {
        if (Auth::user()->isFuelman()) {
            abort(403, 'Fuelman tidak memiliki akses ke rekap dan analisis BBM.');
        }

        $approvedReports = DailyReport::where('status', 'approved')->orderBy('date', 'desc')->get();
        
        $usageData = DailyReportItem::whereHas('dailyReport', function($q) {
                $q->where('status', 'approved');
            })
            ->select('tank_id', DB::raw('SUM(fm_pakai) as total_pakai'))
            ->groupBy('tank_id')
            ->with('tank')
            ->get();

        return view('reports.analytics', compact('approvedReports', 'usageData'));
    }
}
