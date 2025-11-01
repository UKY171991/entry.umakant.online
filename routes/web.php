<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/dashboard');
    }
    return view('welcome');
});

Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::resource('clients', App\Http\Controllers\ClientController::class);

Route::resource('incomes', App\Http\Controllers\IncomeController::class)->middleware('auth');

Route::resource('expenses', App\Http\Controllers\ExpenseController::class);

Route::post('emails/send/{id}', [App\Http\Controllers\EmailController::class, 'sendEmail'])->name('emails.send');
Route::post('emails/whatsapp/{id}', [App\Http\Controllers\EmailController::class, 'sendWhatsAppMessage'])->name('emails.whatsapp');
Route::post('emails/template-preview', [App\Http\Controllers\EmailController::class, 'templatePreview'])->name('emails.template-preview');
Route::post('emails/send-template', [App\Http\Controllers\EmailController::class, 'sendTemplate'])->name('emails.send-template');
Route::post('emails/whatsapp-message', [App\Http\Controllers\EmailController::class, 'generateWhatsAppMessage'])->name('emails.whatsapp-message');
Route::resource('emails', App\Http\Controllers\EmailController::class);

Route::post('/send-website-development-update-email', [App\Http\Controllers\EmailController::class, 'sendWebsiteDevelopmentUpdateEmail']);

Route::get('/test-email', function () {
    try {
        $to = 'test@example.com'; // Replace with a valid email for testing
        $subject = 'Test Email from Laravel';
        $body = 'This is a test email sent directly from a route.';

        \Illuminate\Support\Facades\Mail::to($to)->send(new \App\Mail\PendingTaskReminder($subject, $body));

        return 'Email sent successfully!';
    } catch (\Exception $e) {
        return 'Failed to send email: ' . $e->getMessage();
    }
});

Route::resource('websites', App\Http\Controllers\WebsiteController::class);
Route::post('websites/{id}/test', [App\Http\Controllers\WebsiteController::class, 'testWebsite'])->name('websites.test');
Route::post('websites/check-all', [App\Http\Controllers\WebsiteController::class, 'checkAllWebsites'])->name('websites.check-all');
Route::resource('pending-tasks', App\Http\Controllers\PendingTaskController::class);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // User Settings Routes
    Route::get('/settings', [App\Http\Controllers\UserSettingsController::class, 'index'])->name('settings.index');
    Route::get('/settings/target-income', [App\Http\Controllers\UserSettingsController::class, 'getTargetIncome'])->name('settings.target-income.get');
    Route::post('/settings/target-income', [App\Http\Controllers\UserSettingsController::class, 'updateTargetIncome'])->name('settings.target-income.update');
    Route::delete('/settings/target-income', [App\Http\Controllers\UserSettingsController::class, 'clearTargetIncome'])->name('settings.target-income.clear');
    
    // Inbox Dashboard Route
    Route::get('inbox', [App\Http\Controllers\InboxController::class, 'index'])->name('inbox.dashboard');
    
    // Email Transaction System Routes
    // Email Configuration Routes
    Route::resource('email-configurations', App\Http\Controllers\EmailConfigurationController::class);
    Route::post('email-configurations/{emailConfiguration}/test-connection', [App\Http\Controllers\EmailConfigurationController::class, 'testConnection'])->name('email-configurations.test-connection');
    Route::post('email-configurations/{emailConfiguration}/toggle-status', [App\Http\Controllers\EmailConfigurationController::class, 'toggleStatus'])->name('email-configurations.toggle-status');
    Route::post('email-configurations/{emailConfiguration}/manual-sync', [App\Http\Controllers\EmailConfigurationController::class, 'manualSync'])->name('email-configurations.manual-sync');
    
    // Email Transaction Routes
    Route::resource('email-transactions', App\Http\Controllers\EmailTransactionController::class)->only(['index', 'show', 'edit', 'update', 'destroy']);
    
    // Debug route for testing AJAX
    Route::post('debug/ajax-test', function(Request $request) {
        return response()->json([
            'success' => true,
            'message' => 'AJAX is working correctly!',
            'request_data' => $request->all(),
            'headers' => $request->headers->all()
        ]);
    })->name('debug.ajax-test');
});

require __DIR__.'/auth.php';
