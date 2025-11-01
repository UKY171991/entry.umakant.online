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
        // Get current month and year
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        // Get counts for dashboard cards
        $stats = [
            'total_clients' => Client::count(),
            'total_emails' => Email::count(),
            'total_websites' => Website::count(),
            'total_tasks' => PendingTask::count(),
            'total_income' => Income::sum('total_amount') ?? 0,
            'total_expenses' => Expense::sum('amount') ?? 0,
            'current_month_income' => Income::currentMonth()->sum('total_amount') ?? 0,
            'current_month_expenses' => Expense::whereYear('date', $currentYear)
                                           ->whereMonth('date', $currentMonth)
                                           ->sum('amount') ?? 0,
        ];
        
        $stats['net_profit'] = $stats['total_income'] - $stats['total_expenses'];
        $stats['current_month_net'] = $stats['current_month_income'] - $stats['current_month_expenses'];
        
        // Get monthly data for the last 12 months
        $monthlyIncomes = $this->getMonthlyIncomes();
        $monthlyExpenses = $this->getMonthlyExpenses();
        
        // Get income comparison data
        $comparisonData = $this->getIncomeComparisonData();
        
        return view('dashboard', compact('monthlyIncomes', 'monthlyExpenses', 'stats', 'comparisonData'));
    }
    
    private function getMonthlyIncomes()
    {
        $monthlyData = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->format('M Y');
            
            $total = Income::forMonth($date->year, $date->month)
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
    
    /**
     * Get income comparison data for monthly and yearly targets
     *
     * @return array
     */
    private function getIncomeComparisonData()
    {
        try {
            $user = auth()->user();
            
            // If no user is authenticated, return empty data
            if (!$user) {
                \Log::info('No authenticated user found for income comparison');
                return $this->getEmptyComparisonData();
            }
            
            // If no target income is set or target is invalid, return empty data
            if (!$user->daily_target_income || $user->daily_target_income <= 0) {
                \Log::info('No valid daily target income set for user: ' . $user->id);
                return $this->getEmptyComparisonData();
            }
            
            // Get current date safely with validation
            $now = now();
            if (!$now || !$now->isValid()) {
                throw new \Exception('Invalid current date');
            }
            
            // Monthly calculations with error handling
            $monthlyData = $this->calculateMonthlyComparison($user, $now);
            
            // Yearly calculations with error handling
            $yearlyData = $this->calculateYearlyComparison($user, $now);
            
            return [
                'monthly' => $monthlyData,
                'yearly' => $yearlyData
            ];
            
        } catch (\Exception $e) {
            // Log the error for debugging with more context
            \Log::error('Error calculating income comparison data: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return safe fallback data
            return $this->getEmptyComparisonData();
        }
    }
    
    /**
     * Calculate monthly comparison data with error handling
     *
     * @param \App\Models\User $user
     * @param \Carbon\Carbon $now
     * @return array
     */
    private function calculateMonthlyComparison($user, $now)
    {
        try {
            // Get monthly actual income with fallback and null handling
            $monthlyActual = 0;
            try {
                $monthlyActualRaw = Income::currentMonth()->sum('total_amount');
                $monthlyActual = is_numeric($monthlyActualRaw) && $monthlyActualRaw !== null ? floatval($monthlyActualRaw) : 0;
            } catch (\Exception $dbException) {
                \Log::error('Database error getting monthly income: ' . $dbException->getMessage());
                $monthlyActual = 0;
            }
            
            // Calculate days in month with edge case handling
            $daysInMonth = $now->daysInMonth;
            if ($daysInMonth <= 0 || $daysInMonth > 31) {
                throw new \Exception('Invalid days in month: ' . $daysInMonth);
            }
            
            // Calculate monthly target with overflow protection
            $dailyTarget = floatval($user->daily_target_income ?? 0);
            
            // Handle edge case where daily target is extremely large
            if ($dailyTarget > 1000000) { // Cap at 1M per day to prevent overflow
                \Log::warning('Daily target income is unusually high: ' . $dailyTarget);
                $dailyTarget = 1000000;
            }
            
            $monthlyTarget = $dailyTarget * $daysInMonth;
            
            // Prevent division by zero and calculate percentage
            $percentage = 0;
            if ($monthlyTarget > 0) {
                $percentage = ($monthlyActual / $monthlyTarget) * 100;
                // Handle potential infinity or NaN results
                if (!is_finite($percentage)) {
                    $percentage = 0;
                }
            }
            
            // Ensure percentage is within reasonable bounds
            $percentage = max(0, min($percentage, 9999)); // Cap at 9999% to prevent display issues
            
            return [
                'actual' => $monthlyActual,
                'target' => $monthlyTarget,
                'percentage' => round($percentage, 2),
                'difference' => $monthlyActual - $monthlyTarget,
                'has_target' => true,
                'period' => $now->format('M Y')
            ];
            
        } catch (\Exception $e) {
            \Log::error('Error calculating monthly comparison: ' . $e->getMessage());
            return $this->getEmptyMonthlyData();
        }
    }
    
    /**
     * Calculate yearly comparison data with error handling
     *
     * @param \App\Models\User $user
     * @param \Carbon\Carbon $now
     * @return array
     */
    private function calculateYearlyComparison($user, $now)
    {
        try {
            // Get yearly actual income with fallback and null handling
            $yearlyActual = 0;
            try {
                $yearlyActualRaw = Income::currentYear()->sum('total_amount');
                $yearlyActual = is_numeric($yearlyActualRaw) && $yearlyActualRaw !== null ? floatval($yearlyActualRaw) : 0;
            } catch (\Exception $dbException) {
                \Log::error('Database error getting yearly income: ' . $dbException->getMessage());
                $yearlyActual = 0;
            }
            
            // Calculate days in year with leap year handling
            $daysInYear = $now->isLeapYear() ? 366 : 365;
            if ($daysInYear <= 0 || $daysInYear > 366) {
                throw new \Exception('Invalid days in year: ' . $daysInYear);
            }
            
            // Calculate yearly target with overflow protection
            $dailyTarget = floatval($user->daily_target_income ?? 0);
            
            // Handle edge case where daily target is extremely large
            if ($dailyTarget > 1000000) { // Cap at 1M per day to prevent overflow
                \Log::warning('Daily target income is unusually high: ' . $dailyTarget);
                $dailyTarget = 1000000;
            }
            
            $yearlyTarget = $dailyTarget * $daysInYear;
            
            // Prevent division by zero and calculate percentage
            $percentage = 0;
            if ($yearlyTarget > 0) {
                $percentage = ($yearlyActual / $yearlyTarget) * 100;
                // Handle potential infinity or NaN results
                if (!is_finite($percentage)) {
                    $percentage = 0;
                }
            }
            
            // Ensure percentage is within reasonable bounds
            $percentage = max(0, min($percentage, 9999)); // Cap at 9999% to prevent display issues
            
            return [
                'actual' => $yearlyActual,
                'target' => $yearlyTarget,
                'percentage' => round($percentage, 2),
                'difference' => $yearlyActual - $yearlyTarget,
                'has_target' => true,
                'period' => $now->format('Y')
            ];
            
        } catch (\Exception $e) {
            \Log::error('Error calculating yearly comparison: ' . $e->getMessage());
            return $this->getEmptyYearlyData();
        }
    }
    
    /**
     * Get empty comparison data structure
     *
     * @return array
     */
    private function getEmptyComparisonData()
    {
        return [
            'monthly' => $this->getEmptyMonthlyData(),
            'yearly' => $this->getEmptyYearlyData()
        ];
    }
    
    /**
     * Get empty monthly data structure
     *
     * @return array
     */
    private function getEmptyMonthlyData()
    {
        return [
            'actual' => 0,
            'target' => 0,
            'percentage' => 0,
            'difference' => 0,
            'has_target' => false,
            'period' => now()->format('M Y')
        ];
    }
    
    /**
     * Get empty yearly data structure
     *
     * @return array
     */
    private function getEmptyYearlyData()
    {
        return [
            'actual' => 0,
            'target' => 0,
            'percentage' => 0,
            'difference' => 0,
            'has_target' => false,
            'period' => now()->format('Y')
        ];
    }
}
