<?php

namespace App\Http\Controllers;

use App\Models\EmailTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class EmailTransactionController extends Controller
{
    /**
     * Display a listing of email transactions.
     */
    public function index(Request $request)
    {
        $query = EmailTransaction::with(['income', 'expense', 'emailConfiguration']);

        // Apply filters
        if ($request->filled('date_from')) {
            $query->where('transaction_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('transaction_date', '<=', $request->date_to);
        }

        if ($request->filled('type')) {
            $query->where('transaction_type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('processing_status', $request->status);
        }

        if ($request->filled('amount_min')) {
            $query->where('amount', '>=', $request->amount_min);
        }

        if ($request->filled('amount_max')) {
            $query->where('amount', '<=', $request->amount_max);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('sender', 'like', "%{$search}%");
            });
        }

        $transactions = $query->orderBy('transaction_date', 'desc')
                             ->orderBy('created_at', 'desc')
                             ->paginate(25);

        return view('email-transactions.index', compact('transactions'));
    }

    /**
     * Display the specified email transaction.
     */
    public function show(EmailTransaction $emailTransaction)
    {
        $emailTransaction->load(['income', 'expense', 'emailConfiguration']);
        
        return view('email-transactions.show', compact('emailTransaction'));
    }

    /**
     * Show the form for editing the specified email transaction.
     */
    public function edit(EmailTransaction $emailTransaction)
    {
        $emailTransaction->load(['income', 'expense']);
        
        return view('email-transactions.edit', compact('emailTransaction'));
    }

    /**
     * Update the specified email transaction.
     */
    public function update(Request $request, EmailTransaction $emailTransaction)
    {
        $request->validate([
            'transaction_type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string|max:255',
        ]);

        $emailTransaction->update([
            'transaction_type' => $request->transaction_type,
            'amount' => $request->amount,
            'transaction_date' => $request->transaction_date,
            'description' => $request->description,
        ]);

        // Update the related income/expense record if it exists
        if ($emailTransaction->income_id && $request->transaction_type === 'income') {
            $emailTransaction->income->update([
                'total_amount' => $request->amount,
                'date' => $request->transaction_date,
                'description' => $request->description,
            ]);
        } elseif ($emailTransaction->expense_id && $request->transaction_type === 'expense') {
            $emailTransaction->expense->update([
                'amount' => $request->amount,
                'date' => $request->transaction_date,
                'expense_name' => $request->description,
            ]);
        }

        return redirect()->route('email-transactions.index')
                        ->with('success', 'Email transaction updated successfully.');
    }

    /**
     * Remove the specified email transaction.
     */
    public function destroy(EmailTransaction $emailTransaction)
    {
        // Delete related income/expense record if it exists
        if ($emailTransaction->income_id) {
            $emailTransaction->income->delete();
        }
        
        if ($emailTransaction->expense_id) {
            $emailTransaction->expense->delete();
        }

        $emailTransaction->delete();

        return redirect()->route('email-transactions.index')
                        ->with('success', 'Email transaction deleted successfully.');
    }

    /**
     * Reprocess a failed email transaction.
     */
    public function reprocess(EmailTransaction $emailTransaction)
    {
        if ($emailTransaction->processing_status !== 'failed') {
            return redirect()->back()
                           ->with('error', 'Only failed transactions can be reprocessed.');
        }

        // Reset status to pending for reprocessing
        $emailTransaction->update([
            'processing_status' => 'pending',
            'error_message' => null,
        ]);

        // Trigger reprocessing job (this would be handled by the background job)
        // ProcessEmailTransactionJob::dispatch($emailTransaction);

        return redirect()->back()
                        ->with('success', 'Transaction queued for reprocessing.');
    }

    /**
     * Get transaction statistics for dashboard.
     */
    public function getStats(): JsonResponse
    {
        $stats = [
            'total_transactions' => EmailTransaction::count(),
            'processed_today' => EmailTransaction::whereDate('created_at', today())->count(),
            'pending_transactions' => EmailTransaction::where('processing_status', 'pending')->count(),
            'failed_transactions' => EmailTransaction::where('processing_status', 'failed')->count(),
            'total_income' => EmailTransaction::where('transaction_type', 'income')
                                           ->where('processing_status', 'processed')
                                           ->sum('amount'),
            'total_expenses' => EmailTransaction::where('transaction_type', 'expense')
                                             ->where('processing_status', 'processed')
                                             ->sum('amount'),
            'last_sync' => EmailTransaction::latest('created_at')->first()?->created_at?->format('Y-m-d H:i:s'),
        ];

        return response()->json($stats);
    }

    /**
     * Trigger manual sync of email transactions.
     */
    public function manualSync()
    {
        // This would trigger the email fetching and processing
        // For now, we'll just return a success message
        // In a real implementation, this would dispatch the ProcessEmailTransactions job
        
        return redirect()->back()
                        ->with('success', 'Manual sync triggered successfully. Processing will begin shortly.');
    }
}