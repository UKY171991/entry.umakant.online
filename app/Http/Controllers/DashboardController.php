<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Income;
use App\Models\Expense;
use App\Models\Email;
use App\Models\Website;
use App\Models\PendingTask;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Get counts for dashboard cards
        $stats = [
            'total_clients' => Client::count(),
            'total_emails' => Email::count(),
            'total_websites' => Website::count(),
            'total_tasks' => PendingTask::count(),
            'total_income' => Income::sum('total_amount') ?? 0,
            'total_expenses' => Expense::sum('amount') ?? 0,
        ];
        
        $stats['net_profit'] = $stats['total_income'] - $stats['total_expenses'];
        
        // Get monthly data for the last 12 months
        $monthlyIncomes = $this->getMonthlyIncomes();
        $monthlyExpenses = $this->getMonthlyExpenses();
        
        return view('dashboard', compact('monthlyIncomes', 'monthlyExpenses', 'stats'));
    }
    
    private function getMonthlyIncomes()
    {
        $monthlyData = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->format('M Y');
            
            $total = Income::whereYear('date', $date->year)
                          ->whereMonth('date', $date->month)
                          ->sum('total_amount') ?? 0;
            
            $monthlyData[] = [
                'month' => $monthName,
                'total' => floatval($total)
            ];
        }
        
        return $monthlyData;
    }
    
    private function getMonthlyExpenses()
    {
        $monthlyData = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->format('M Y');
            
            $total = Expense::whereYear('date', $date->year)
                           ->whereMonth('date', $date->month)
                           ->sum('amount') ?? 0;
            
            $monthlyData[] = [
                'month' => $monthName,
                'total' => floatval($total)
            ];
        }
        
        return $monthlyData;
    }
}
