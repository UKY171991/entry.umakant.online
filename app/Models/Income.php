<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Income extends Model
{
    use HasFactory;
    protected $fillable = [
        'client_id',
        'total_amount',
        'pending_amount',
        'received_amount',
        'date',
        'source',
        'description',
        'email_transaction_id'
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the email transaction that created this income (if any)
     */
    public function emailTransaction()
    {
        return $this->belongsTo(EmailTransaction::class);
    }

    /**
     * Scope to filter income entries for the current month
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeCurrentMonth(Builder $query): Builder
    {
        try {
            $now = now();
            return $query->whereYear('date', $now->year)
                        ->whereMonth('date', $now->month);
        } catch (\Exception $e) {
            // Fallback to a safe query that returns no results
            \Log::error('Error in currentMonth scope: ' . $e->getMessage());
            return $query->whereRaw('1 = 0');
        }
    }

    /**
     * Scope to filter income entries for the current year
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeCurrentYear(Builder $query): Builder
    {
        try {
            $now = now();
            if (!$now || !$now->isValid()) {
                throw new \Exception('Invalid current date');
            }
            
            $currentYear = $now->year;
            if ($currentYear < 1900 || $currentYear > 2100) {
                throw new \Exception('Invalid year: ' . $currentYear);
            }
            
            return $query->whereYear('date', $currentYear);
        } catch (\Exception $e) {
            // Fallback to a safe query that returns no results
            \Log::error('Error in currentYear scope: ' . $e->getMessage());
            return $query->whereRaw('1 = 0');
        }
    }

    /**
     * Scope to filter income entries for a specific month
     *
     * @param Builder $query
     * @param int $year
     * @param int $month
     * @return Builder
     */
    public function scopeForMonth(Builder $query, int $year, int $month): Builder
    {
        // Validate input parameters
        if ($year < 1900 || $year > 2100 || $month < 1 || $month > 12) {
            \Log::warning("Invalid year ($year) or month ($month) provided to forMonth scope");
            return $query->whereRaw('1 = 0'); // Return empty result set
        }
        
        return $query->whereYear('date', $year)->whereMonth('date', $month);
    }

    /**
     * Scope to filter income entries for a specific year
     *
     * @param Builder $query
     * @param int $year
     * @return Builder
     */
    public function scopeForYear(Builder $query, int $year): Builder
    {
        // Validate year parameter
        if ($year < 1900 || $year > 2100) {
            \Log::warning("Invalid year ($year) provided to forYear scope");
            return $query->whereRaw('1 = 0'); // Return empty result set
        }
        
        return $query->whereYear('date', $year);
    }

    /**
     * Scope to filter income entries within a date range
     *
     * @param Builder $query
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @return Builder
     */
    public function scopeDateRange(Builder $query, $startDate, $endDate): Builder
    {
        try {
            // Convert to Carbon instances if they're strings
            if (is_string($startDate)) {
                $startDate = Carbon::parse($startDate);
            }
            if (is_string($endDate)) {
                $endDate = Carbon::parse($endDate);
            }
            
            // Validate date range
            if ($startDate > $endDate) {
                \Log::warning("Invalid date range: start date ($startDate) is after end date ($endDate)");
                return $query->whereRaw('1 = 0'); // Return empty result set
            }
            
            return $query->whereBetween('date', [$startDate, $endDate]);
        } catch (\Exception $e) {
            \Log::error('Error in dateRange scope: ' . $e->getMessage());
            return $query->whereRaw('1 = 0'); // Return empty result set
        }
    }
}
