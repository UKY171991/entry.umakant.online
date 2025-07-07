<?php

namespace App\Http\Controllers;

use App\Models\Email;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\EmailRequest;
use Illuminate\Support\Facades\Mail;
use Exception;

class EmailController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $draw = $request->get('draw');
            $start = $request->get('start', 0);
            $length = $request->get('length', 10);
            $search_value = '';
            
            // Safely get search value
            $search = $request->get('search');
            if (is_array($search) && isset($search['value'])) {
                $search_value = $search['value'];
            }

            $query = Email::with('client');

            // Apply search filter
            if (!empty($search_value)) {
                $query->where(function($q) use ($search_value) {
                    $q->where('email', 'like', '%' . $search_value . '%')
                      ->orWhereHas('client', function($q) use ($search_value) {
                          $q->where('name', 'like', '%' . $search_value . '%');
                      });
                });
            }

            $query->orderBy('created_at', 'desc');

            $totalRecords = Email::count();
            $filteredRecords = $query->count();
            $emails = $query->offset($start)->limit($length)->get();

            $data = [];
            foreach ($emails as $email) {
                $data[] = [
                    'id' => $email->id,
                    'client_name' => $email->client ? $email->client->name : 'N/A',
                    'email' => $email->email,
                    'updated_at' => $email->updated_at->format('Y-m-d H:i:s'),
                    'action' => '<button type="button" class="btn btn-info btn-sm editEmail" data-id="'.$email->id.'"><i class="fas fa-edit"></i> Edit</button> <button type="button" class="btn btn-danger btn-sm deleteEmail" data-id="'.$email->id.'"><i class="fas fa-trash"></i> Delete</button>',
                    'send_email' => '<button type="button" class="btn btn-success btn-sm sendEmail" data-id="'.$email->id.'" data-email="'.$email->email.'"><i class="fas fa-envelope"></i> Send Email</button>'
                ];
            }

            return response()->json([
                "draw" => intval($draw),
                "recordsTotal" => intval($totalRecords),
                "recordsFiltered" => intval($filteredRecords),
                "data" => $data
            ]);
        }

        $clients = Client::orderBy('name')->get();
        return view('emails.index', compact('clients'));
    }

    public function create()
    {
        // Return redirect to index for modal-based creation
        return redirect()->route('emails.index');
    }

    public function store(EmailRequest $request)
    {
        // Validation is automatically handled by EmailRequest
        $client = Client::firstOrCreate(['name' => $request->client_name]);
        $clientId = $client->id;

        $email = Email::updateOrCreate(
            ['id' => $request->email_id],
            [
                'client_id' => $clientId,
                'email' => $request->email,
                'email_template' => $request->email_template,
                'project_name' => $request->project_name,
                'estimated_cost' => $request->estimated_cost,
                'timeframe' => $request->timeframe,
                'notes' => $request->notes,
            ]
        );

        return response()->json(['success' => 'Email saved successfully!', 'email' => $email]);
    }

    public function show(string $id)
    {
        $email = Email::find($id);
        if (!$email) {
            return response()->json(['error' => 'Email not found'], 404);
        }
        return response()->json($email);
    }

    public function edit(string $id)
    {
        $email = Email::with('client')->find($id);
        if (!$email) {
            return response()->json(['error' => 'Email not found'], 404);
        }
        return response()->json([
            'id' => $email->id,
            'client_id' => $email->client_id,
            'client_name' => $email->client ? $email->client->name : null,
            'email' => $email->email,
            'email_template' => $email->email_template,
            'project_name' => $email->project_name,
            'estimated_cost' => $email->estimated_cost,
            'timeframe' => $email->timeframe,
            'notes' => $email->notes,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $email = Email::find($id);
        if (!$email) {
            return response()->json(['error' => 'Email not found'], 404);
        }

        $client = Client::firstOrCreate(['name' => $request->client_name]);
        $clientId = $client->id;

        $email->update([
            'client_id' => $clientId,
            'email' => $request->email,
            'email_template' => $request->email_template,
            'project_name' => $request->project_name,
            'estimated_cost' => $request->estimated_cost,
            'timeframe' => $request->timeframe,
            'notes' => $request->notes,
        ]);

        return response()->json(['success' => 'Email updated successfully!', 'email' => $email]);
    }

    public function destroy(string $id)
    {
        $email = Email::find($id);
        if ($email) {
            $email->delete();
            return response()->json(['success' => 'Email deleted successfully!']);
        }
        return response()->json(['error' => 'Email not found'], 404);
    }

    public function sendEmail(Request $request, $id)
    {
        try {
            $email = Email::with('client')->find($id);
            
            if (!$email) {
                return response()->json(['message' => 'Email record not found'], 404);
            }

            $to = $email->email;
            $clientName = $email->client ? $email->client->name : 'Valued Client';
            $template = $email->email_template;
            
            // If no template is specified, use general inquiry as default
            if (!$template) {
                $template = 'general_inquiry';
            }
            
            // Prepare email data
            $emailData = [
                'clientName' => $clientName,
                'email' => $to,
                'projectName' => $email->project_name,
                'estimatedCost' => $email->estimated_cost,
                'timeframe' => $email->timeframe,
                'notes' => $email->notes,
                'progressPercentage' => 75, // Default progress for status updates
                'projectDetails' => $email->notes ?: 'Project details will be provided soon.',
                'nextMilestone' => 'Next milestone information will be updated.',
                'websiteUrl' => 'https://example.com', // Default website URL
                'launchDate' => now()->format('Y-m-d'),
                'maintenanceInfo' => 'Our team will provide ongoing support and maintenance.'
            ];
            
            // Send email based on template
            switch ($template) {
                case 'website_proposal':
                    Mail::to($to)->send(new \App\Mail\WebsiteProposal($emailData));
                    break;
                case 'project_update':
                    Mail::to($to)->send(new \App\Mail\ProjectStatusUpdate($emailData));
                    break;
                case 'project_completion':
                    Mail::to($to)->send(new \App\Mail\ProjectCompletion($emailData));
                    break;
                case 'general_inquiry':
                default:
                    Mail::to($to)->send(new \App\Mail\GeneralInquiry($emailData));
                    break;
            }
            
            return response()->json(['message' => 'Email sent successfully to ' . $to]);
            
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to send email.', 'error' => $e->getMessage()], 500);
        }
    }

    public function sendWebsiteDevelopmentUpdateEmail(Request $request)
    {
        $to = $request->input('to');
        $subject = $request->input('subject');
        $body = $request->input('body');

        if (!$to || !$subject || !$body) {
            return response()->json(['message' => 'Missing required parameters: to, subject, or body.'], 400);
        }

        try {
            Mail::to($to)->send(new \App\Mail\WebsiteDevelopmentUpdate($subject, $body));
            return response()->json(['message' => 'Website development update email sent successfully!']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to send website development update email.', 'error' => $e->getMessage()], 500);
        }
    }

    public function templatePreview(Request $request)
    {
        $template = $request->input('template');
        $clientName = $request->input('client_name', 'Client Name');
        $projectName = $request->input('project_name', 'Your Project');
        $estimatedCost = $request->input('estimated_cost', 50000);
        $timeframe = $request->input('timeframe', '2-3 weeks');
        $notes = $request->input('notes', '');

        try {
            switch ($template) {
                case 'website_proposal':
                    return view('emails.templates.website_proposal', compact('clientName', 'projectName', 'estimatedCost', 'timeframe', 'notes'))->render();
                    
                case 'project_update':
                    $progressPercentage = 65;
                    $completedTasks = [
                        'Design mockups approved',
                        'Homepage development completed',
                        'Contact form implemented',
                        'SEO optimization started'
                    ];
                    $upcomingTasks = [
                        'Product pages development',
                        'Payment gateway integration',
                        'Mobile responsiveness testing',
                        'Final review and testing'
                    ];
                    return view('emails.templates.project_status_update', compact('clientName', 'projectName', 'progressPercentage', 'completedTasks', 'upcomingTasks', 'notes'))->render();
                    
                case 'project_completion':
                    $websiteUrl = 'https://www.yourclientwebsite.com';
                    $loginCredentials = [
                        'admin_url' => 'https://www.yourclientwebsite.com/admin',
                        'username' => 'admin',
                        'password' => 'SecurePassword123'
                    ];
                    $supportDetails = '3 months of free support included with regular updates and maintenance.';
                    return view('emails.templates.project_completion', compact('clientName', 'projectName', 'websiteUrl', 'loginCredentials', 'supportDetails'))->render();
                    
                case 'general_inquiry':
                    return view('emails.templates.general_inquiry', compact('clientName'))->render();
                    
                case 'follow_up':
                    return view('emails.templates.general_inquiry', compact('clientName'))->render();
                    
                case 'pathology_management':
                    return view('emails.templates.pathology_management', compact('clientName', 'projectName', 'estimatedCost', 'timeframe', 'notes'))->render();
                    
                case 'hospital_management':
                    return view('emails.templates.hospital_management', compact('clientName', 'projectName', 'estimatedCost', 'timeframe', 'notes'))->render();
                    
                default:
                    return '<div class="alert alert-info">Template preview not available for this template type.</div>';
            }
        } catch (Exception $e) {
            return '<div class="alert alert-danger">Error loading template: ' . $e->getMessage() . '</div>';
        }
    }

    public function sendTemplate(Request $request)
    {
        $template = $request->input('template');
        $email = $request->input('email');
        $clientName = $request->input('client_name', 'Valued Client');
        $projectName = $request->input('project_name', 'Your Project');
        $estimatedCost = $request->input('estimated_cost', 50000);
        $timeframe = $request->input('timeframe', '2-3 weeks');
        $notes = $request->input('notes', '');

        if (!$template || !$email) {
            return response()->json(['message' => 'Template and email are required.'], 400);
        }

        try {
            switch ($template) {
                case 'website_proposal':
                    Mail::to($email)->send(new \App\Mail\WebsiteProposal($clientName, $projectName, $estimatedCost, $timeframe, $notes));
                    break;
                    
                case 'project_update':
                    $progressPercentage = 65;
                    $completedTasks = [
                        'Design mockups approved',
                        'Homepage development completed',
                        'Contact form implemented',
                        'SEO optimization started'
                    ];
                    $upcomingTasks = [
                        'Product pages development',
                        'Payment gateway integration',
                        'Mobile responsiveness testing',
                        'Final review and testing'
                    ];
                    Mail::to($email)->send(new \App\Mail\ProjectStatusUpdate($clientName, $projectName, $progressPercentage, $completedTasks, $upcomingTasks, $notes));
                    break;
                    
                case 'project_completion':
                    $websiteUrl = 'https://www.yourclientwebsite.com';
                    $loginCredentials = [
                        'admin_url' => 'https://www.yourclientwebsite.com/admin',
                        'username' => 'admin',
                        'password' => 'SecurePassword123'
                    ];
                    $supportDetails = '3 months of free support included with regular updates and maintenance.';
                    Mail::to($email)->send(new \App\Mail\ProjectCompletion($clientName, $projectName, $websiteUrl, $loginCredentials, $supportDetails));
                    break;
                    
                case 'general_inquiry':
                case 'follow_up':
                    Mail::to($email)->send(new \App\Mail\GeneralInquiry($clientName));
                    break;
                    
                case 'pathology_management':
                    Mail::to($email)->send(new \App\Mail\PathologyManagement($clientName, $projectName, $estimatedCost, $timeframe, $notes));
                    break;
                    
                case 'hospital_management':
                    Mail::to($email)->send(new \App\Mail\HospitalManagement($clientName, $projectName, $estimatedCost, $timeframe, $notes));
                    break;
                    
                default:
                    return response()->json(['message' => 'Invalid template selected.'], 400);
            }
            
            return response()->json(['message' => 'Email sent successfully!']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to send email: ' . $e->getMessage()], 500);
        }
    }
}
