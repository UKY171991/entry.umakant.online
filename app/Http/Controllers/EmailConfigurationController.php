<?php

namespace App\Http\Controllers;

use App\Contracts\EmailFetcherInterface;
use App\Http\Requests\EmailConfigurationRequest;
use App\Models\EmailConfiguration;
use App\Services\EmailTransactionLoggerService;
use App\Exceptions\EmailTransactionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class EmailConfigurationController extends Controller
{
    public function __construct(
        private EmailFetcherInterface $emailFetcher,
        private EmailTransactionLoggerService $logger
    ) {}

    /**
     * Display a listing of email configurations.
     */
    public function index()
    {
        $configurations = EmailConfiguration::with(['emailTransactions' => function ($query) {
            $query->latest()->limit(5);
        }])->latest()->get();

        return view('email-configurations.index', compact('configurations'));
    }

    /**
     * Show the form for creating a new email configuration.
     */
    public function create()
    {
        return view('email-configurations.create');
    }

    /**
     * Store a newly created email configuration.
     */
    public function store(EmailConfigurationRequest $request)
    {
        try {
            $validated = $request->validated();
            $configuration = EmailConfiguration::create($validated);

            $this->logger->logAuditTrail('created', 'EmailConfiguration', $configuration->id, $validated, auth()->id());

            return redirect()
                ->route('email-configurations.show', $configuration)
                ->with('success', 'Email configuration created successfully.');
                
        } catch (Exception $e) {
            throw new EmailTransactionException(
                'Failed to create email configuration: ' . $e->getMessage(),
                'EmailConfigurationController',
                ['request_data' => $request->validated()],
                500,
                $e
            );
        }
    }

    /**
     * Display the specified email configuration.
     */
    public function show(EmailConfiguration $emailConfiguration)
    {
        try {
            // Ensure we have a valid email configuration
            if (!$emailConfiguration || !$emailConfiguration->exists) {
                throw new EmailTransactionException(
                    'Email configuration not found',
                    'EmailConfigurationController',
                    [],
                    404
                );
            }

            $emailConfiguration->load([
                'emailTransactions' => function ($query) {
                    $query->latest()->limit(10);
                }
            ]);

            $stats = [
                'total_transactions' => $emailConfiguration->emailTransactions()->count(),
                'processed_transactions' => $emailConfiguration->processedTransactions()->count(),
                'failed_transactions' => $emailConfiguration->failedTransactions()->count(),
                'last_sync' => $emailConfiguration->last_sync_at?->diffForHumans(),
            ];

            return view('email-configurations.show', compact('emailConfiguration', 'stats'));
        } catch (EmailTransactionException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new EmailTransactionException(
                'Failed to display email configuration: ' . $e->getMessage(),
                'EmailConfigurationController',
                ['configuration_id' => $emailConfiguration?->id],
                500,
                $e
            );
        }
    }    /**
 
    * Show the form for editing the specified email configuration.
     */
    public function edit(EmailConfiguration $emailConfiguration)
    {
        return view('email-configurations.edit', compact('emailConfiguration'));
    }

    /**
     * Update the specified email configuration.
     */
    public function update(EmailConfigurationRequest $request, EmailConfiguration $emailConfiguration)
    {
        try {
            // Ensure we have a valid email configuration
            if (!$emailConfiguration || !$emailConfiguration->exists) {
                throw new EmailTransactionException(
                    'Email configuration not found',
                    'EmailConfigurationController',
                    ['request_data' => $request->all()],
                    404
                );
            }

            $validated = $request->validated();
            $originalData = $emailConfiguration->toArray();
            
            // Only update password if provided
            if (empty($validated['password'])) {
                unset($validated['password']);
            }

            $emailConfiguration->update($validated);

            $this->logger->logAuditTrail('updated', 'EmailConfiguration', $emailConfiguration->id, [
                'original' => $originalData,
                'updated' => $validated
            ], auth()->id());

            return redirect()
                ->route('email-configurations.show', $emailConfiguration)
                ->with('success', 'Email configuration updated successfully.');
                
        } catch (EmailTransactionException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new EmailTransactionException(
                'Failed to update email configuration: ' . $e->getMessage(),
                'EmailConfigurationController',
                ['configuration_id' => $emailConfiguration?->id, 'request_data' => $request->all()],
                500,
                $e
            );
        }
    }

    /**
     * Remove the specified email configuration.
     */
    public function destroy(EmailConfiguration $emailConfiguration, Request $request)
    {
        try {
            $name = $emailConfiguration->name;
            $configId = $emailConfiguration->id;
            
            $emailConfiguration->delete();

            $this->logger->logAuditTrail('deleted', 'EmailConfiguration', $configId, [
                'name' => $name
            ], auth()->id());

            // Return JSON response for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Email configuration '{$name}' deleted successfully."
                ]);
            }

            return redirect()
                ->route('email-configurations.index')
                ->with('success', "Email configuration '{$name}' deleted successfully.");
                
        } catch (Exception $e) {
            Log::error('Failed to delete email configuration', [
                'config_id' => $emailConfiguration->id,
                'error' => $e->getMessage()
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete configuration: ' . $e->getMessage()
                ], 500);
            }

            return redirect()
                ->back()
                ->withErrors(['error' => 'Failed to delete configuration: ' . $e->getMessage()]);
        }
    }

    /**
     * Test connection for the specified email configuration.
     */
    public function testConnection(EmailConfiguration $emailConfiguration)
    {
        try {
            $success = $this->emailFetcher->testConnection($emailConfiguration);
            
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Connection successful! Email configuration is working properly.'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Connection test failed. Please check your email settings.'
                ], 400);
            }
        } catch (Exception $e) {
            Log::error('Email connection test failed', [
                'config_id' => $emailConfiguration->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle active status of email configuration.
     */
    public function toggleStatus(EmailConfiguration $emailConfiguration, Request $request)
    {
        try {
            $originalStatus = $emailConfiguration->is_active;
            $emailConfiguration->update([
                'is_active' => !$emailConfiguration->is_active
            ]);

            $status = $emailConfiguration->is_active ? 'activated' : 'deactivated';
            
            $this->logger->logAuditTrail('updated', 'EmailConfiguration', $emailConfiguration->id, [
                'is_active' => ['from' => $originalStatus, 'to' => $emailConfiguration->is_active]
            ], auth()->id());
            
            // Return JSON response for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Email configuration {$status} successfully.",
                    'new_status' => $emailConfiguration->is_active,
                    'status_text' => $emailConfiguration->is_active ? 'Active' : 'Inactive'
                ]);
            }
            
            return redirect()
                ->back()
                ->with('success', "Email configuration {$status} successfully.");
                
        } catch (Exception $e) {
            Log::error('Failed to toggle email configuration status', [
                'config_id' => $emailConfiguration->id,
                'error' => $e->getMessage()
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update status: ' . $e->getMessage()
                ], 500);
            }

            return redirect()
                ->back()
                ->withErrors(['error' => 'Failed to update status: ' . $e->getMessage()]);
        }
    }

    /**
     * Manually sync emails for the specified configuration.
     */
    public function manualSync(EmailConfiguration $emailConfiguration)
    {
        try {
            $emails = $this->emailFetcher->fetchNewEmails($emailConfiguration);
            
            $this->logger->logAuditTrail('manual_sync', 'EmailConfiguration', $emailConfiguration->id, [
                'emails_found' => $emails->count()
            ], auth()->id());
            
            return response()->json([
                'success' => true,
                'message' => "Found {$emails->count()} new emails to process.",
                'count' => $emails->count()
            ]);
        } catch (Exception $e) {
            Log::error('Manual email sync failed', [
                'config_id' => $emailConfiguration->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Manual sync failed: ' . $e->getMessage()
            ], 500);
        }
    }
}