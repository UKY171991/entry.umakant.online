<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::resource('clients', App\Http\Controllers\ClientController::class);

Route::resource('incomes', App\Http\Controllers\IncomeController::class);

Route::resource('expenses', App\Http\Controllers\ExpenseController::class);

Route::post('emails/send/{id}', [App\Http\Controllers\EmailController::class, 'sendEmail'])->name('emails.send');
Route::post('emails/template-preview', [App\Http\Controllers\EmailController::class, 'templatePreview'])->name('emails.template-preview');
Route::post('emails/send-template', [App\Http\Controllers\EmailController::class, 'sendTemplate'])->name('emails.send-template');
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
Route::resource('pending-tasks', App\Http\Controllers\PendingTaskController::class);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
