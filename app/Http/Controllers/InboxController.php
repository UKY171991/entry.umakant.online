<?php

namespace App\Http\Controllers;

use App\Models\EmailConfiguration;
use App\Models\EmailTransaction;
use Illuminate\Http\Request;

class InboxController extends Controller
{
    /**
     * Display the inbox dashboard
     */
    public function index()
    {
        $stats = [
            'total_configurations' => EmailConfiguration::count(),
            'active_configurations' => EmailConfiguration::where('is_active', true)->count(),
            'total_transactions' => EmailTransaction::count(),
            'processed_transactions' => EmailTransaction::where('processing_status', 'processed')->count(),
            'failed_transactions' => EmailTransaction::where('processing_status', 'failed')->count(),
            'pending_transactions' => EmailTransaction::where('processing_status', 'pending')->count(),
        ];

        $recentTransactions = EmailTransaction::with('emailConfiguration')
            ->latest()
            ->limit(10)
            ->get();

        $configurations = EmailConfiguration::with(['emailTransactions' => function ($query) {
            $query->latest()->limit(3);
        }])->get();

        return view('inbox.dashboard', compact('stats', 'recentTransactions', 'configurations'));
    }
}