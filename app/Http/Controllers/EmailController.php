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
                // Create grouped action buttons
                $actionButtons = '
                    <div class="btn-group" role="group" aria-label="Actions">
                        <button type="button" class="btn btn-info btn-sm editEmail" data-id="'.$email->id.'" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm deleteEmail" data-id="'.$email->id.'" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>';
                
                // Create grouped send buttons
                $sendButtons = '
                    <div class="btn-group" role="group" aria-label="Send Messages">
                        <button type="button" class="btn btn-success btn-sm sendEmail" data-id="'.$email->id.'" data-email="'.$email->email.'" title="Send Email">
                            <i class="fas fa-envelope"></i>
                        </button>';
                
                if ($email->phone) {
                    $sendButtons .= '
                        <button type="button" class="btn btn-warning btn-sm previewWhatsApp" data-id="'.$email->id.'" title="Preview WhatsApp Message">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-success btn-sm sendWhatsApp" data-id="'.$email->id.'" data-phone="'.$email->phone.'" title="Send WhatsApp">
                            <i class="fab fa-whatsapp"></i>
                        </button>';
                } else {
                    $sendButtons .= '
                        <button type="button" class="btn btn-secondary btn-sm" disabled title="No Phone Number">
                            <i class="fab fa-whatsapp"></i>
                        </button>';
                }
                
                $sendButtons .= '</div>';

                $data[] = [
                    'id' => $email->id,
                    'client_name' => $email->client ? $email->client->name : 'N/A',
                    'email' => $email->email,
                    'updated_at' => $email->updated_at->format('Y-m-d H:i:s'),
                    'last_email_sent_at' => $email->last_email_sent_at ? $email->last_email_sent_at->format('Y-m-d H:i:s') : 'Never',
                    'last_whatsapp_sent_at' => $email->last_whatsapp_sent_at ? $email->last_whatsapp_sent_at->format('Y-m-d H:i:s') : 'Never',
                    'action' => $actionButtons,
                    'send_buttons' => $sendButtons
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
                'phone' => $request->phone,
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
            'phone' => $email->phone,
            'email_template' => $email->email_template,
            'project_name' => $email->project_name,
            'estimated_cost' => $email->estimated_cost,
            'timeframe' => $email->timeframe,
            'notes' => $email->notes,
        ]);
    }

    public function update(EmailRequest $request, string $id)
    {
        $email = Email::find($id);
        if (!$email) {
            return response()->json(['error' => 'Email not found'], 404);
        }

        $client = Client::firstOrCreate(['name' => $request->client_name]);
        $clientId = $client->id;

        $email->update([
            'client_id' => $clientId,
            'email' => $request->email,
            'phone' => $request->phone,
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
            
            // Update the last email sent timestamp
            $email->update(['last_email_sent_at' => now()]);
            
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
    
    public function generateWhatsAppMessage(Request $request)
    {
        $template = $request->input('template');
        $clientName = $request->input('client_name', 'Valued Client');
        $projectName = $request->input('project_name', 'Your Project');
        $estimatedCost = $request->input('estimated_cost', 50000);
        $timeframe = $request->input('timeframe', '2-3 weeks');
        $notes = $request->input('notes', '');

        try {
            $message = '';
            
            switch ($template) {
                case 'website_proposal':
                    $message = "🚀 *Website Development Proposal*\n\n";
                    $message .= "Dear {$clientName},\n\n";
                    $message .= "Thank you for considering our web development services. Here's our proposal:\n\n";
                    $message .= "📋 *Project:* {$projectName}\n";
                    $message .= "💰 *Investment:* ₹" . number_format($estimatedCost) . "\n";
                    $message .= "⏰ *Timeline:* {$timeframe}\n\n";
                    if ($notes) {
                        $message .= "📝 *Requirements:* {$notes}\n\n";
                    }
                    $message .= "✅ *What's Included:*\n";
                    $message .= "• Responsive Design\n• Modern UI/UX\n• SEO Optimization\n• 3 Months Support\n\n";
                    $message .= "Ready to get started? Let's discuss your project!\n\n";
                    break;
                    
                case 'project_update':
                    $message = "📈 *Project Status Update*\n\n";
                    $message .= "Dear {$clientName},\n\n";
                    $message .= "Great progress on your project: {$projectName}\n\n";
                    $message .= "🎯 *Current Progress:* 65% Complete\n";
                    $message .= "✅ *Completed:* Design, Homepage, Contact Form\n";
                    $message .= "🔄 *Upcoming:* Product Pages, Payment Integration\n\n";
                    $message .= "We're on track for completion within {$timeframe}!\n\n";
                    break;
                    
                case 'project_completion':
                    $message = "🎉 *Project Completed Successfully!*\n\n";
                    $message .= "Dear {$clientName},\n\n";
                    $message .= "Congratulations! Your project '{$projectName}' is now live!\n\n";
                    $message .= "🌐 *Your Website:* Ready for launch\n";
                    $message .= "🔧 *Support:* 3 months included\n";
                    $message .= "📊 *Features:* All modules working perfectly\n\n";
                    $message .= "Thank you for choosing CodeApka!\n\n";
                    break;
                    
                case 'general_inquiry':
                    $message = "📧 *Thank You for Your Inquiry!*\n\n";
                    $message .= "Dear {$clientName},\n\n";
                    $message .= "Thank you for reaching out to CodeApka for your web development needs.\n\n";
                    $message .= "🚀 *Our Services:*\n";
                    $message .= "• Website Development\n• E-commerce Solutions\n• Mobile Apps\n• SEO Optimization\n\n";
                    $message .= "Let's schedule a free consultation to discuss your project!\n\n";
                    break;
                    
                case 'pathology_management':
                    $message = "🔬 *Pathology Management System Proposal*\n\n";
                    $message .= "Dear {$clientName},\n\n";
                    $message .= "We're excited to present our comprehensive Pathology Management System.\n\n";
                    $message .= "💰 *Investment:* ₹" . number_format($estimatedCost) . "\n";
                    $message .= "⏰ *Timeline:* {$timeframe}\n\n";
                    $message .= "🔬 *Key Features:*\n";
                    $message .= "• Patient Management\n• Lab Test Catalog\n• Sample Tracking\n• Report Generation\n• Billing Integration\n\n";
                    if ($notes) {
                        $message .= "📝 *Your Requirements:* {$notes}\n\n";
                    }
                    $message .= "NABH & ISO 15189 compliant system!\n\n";
                    break;
                    
                case 'hospital_management':
                    $message = "🏥 *Hospital Management System Proposal*\n\n";
                    $message .= "Dear {$clientName},\n\n";
                    $message .= "Complete Hospital Management Solution for your healthcare facility.\n\n";
                    $message .= "💰 *Investment:* ₹" . number_format($estimatedCost) . "\n";
                    $message .= "⏰ *Timeline:* {$timeframe}\n\n";
                    $message .= "🏥 *Core Modules:*\n";
                    $message .= "• Patient Registration & EMR\n• OPD/IPD Management\n• Pharmacy Integration\n• Billing & Finance\n• Lab & Radiology\n\n";
                    if ($notes) {
                        $message .= "📝 *Your Requirements:* {$notes}\n\n";
                    }
                    $message .= "HMIS & HL7 compliant system!\n\n";
                    break;
                    
                default:
                    $message = "Hello {$clientName},\n\nThank you for your interest in our services. We'll get back to you soon with more details.\n\n";
                    break;
            }
            
            $message .= "🌐 *CodeApka - Web Development Experts*\n";
            $message .= "📧 uky171991@gmail.com\n";
            $message .= "📱 +91-9453619260\n";
            $message .= "🌐 https://codeapka.com";
            
            return response()->json(['message' => $message]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error generating WhatsApp message: ' . $e->getMessage()], 500);
        }
    }

    public function sendWhatsAppMessage(Request $request, $id)
    {
        try {
            $email = Email::with('client')->find($id);
            
            if (!$email) {
                return response()->json(['message' => 'Email record not found'], 404);
            }

            if (!$email->phone) {
                return response()->json(['message' => 'No WhatsApp number available for this contact'], 400);
            }

            // Update the last WhatsApp sent timestamp
            $email->update(['last_whatsapp_sent_at' => now()]);
            
            // Generate WhatsApp message
            $message = $this->generateWhatsAppMessageText($email);
            
            // For now, we'll just return success - in a real implementation, 
            // you would integrate with WhatsApp Business API here
            return response()->json([
                'message' => 'WhatsApp message marked as sent to ' . $email->phone,
                'whatsapp_message' => $message,
                'phone' => $email->phone
            ]);
            
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to send WhatsApp message.', 'error' => $e->getMessage()], 500);
        }
    }

    private function generateWhatsAppMessageText($email)
    {
        $clientName = $email->client ? $email->client->name : 'Valued Client';
        $template = $email->email_template ?: 'general_inquiry';
        
        switch ($template) {
            case 'website_proposal':
                return "Hello {$clientName}! 👋\n\nWe're excited to present our website proposal for {$email->project_name}.\n\n💰 Estimated Cost: ₹{$email->estimated_cost}\n⏰ Timeframe: {$email->timeframe}\n\n📝 {$email->notes}\n\nFor detailed proposal, please check your email. Let's discuss!\n\n📞 Contact: +91-9453619260\n🌐 Visit: entry.umakant.online";
                
            case 'project_status_update':
                return "Hi {$clientName}! 📊\n\nProject Update for {$email->project_name}:\n\n✅ Progress: 75% Complete\n⏰ Timeline: {$email->timeframe}\n\n📋 Latest Updates:\n{$email->notes}\n\nWe're on track for delivery! Any questions?\n\n📞 Contact: +91-9453619260\n🌐 entry.umakant.online";
                
            case 'project_completion':
                return "Congratulations {$clientName}! 🎉\n\n✅ {$email->project_name} is now COMPLETE!\n\n🚀 Your project is live and ready\n📋 All deliverables completed\n\n{$email->notes}\n\nThank you for choosing us!\n\n📞 Support: +91-9453619260\n🌐 entry.umakant.online";
                
            case 'pathology_management':
                return "Hello {$clientName}! 🏥\n\nYour Pathology Management System is ready!\n\n💰 Investment: ₹{$email->estimated_cost}\n⏰ Timeline: {$email->timeframe}\n\n🔬 Features: Complete lab management, reports, billing\n📊 Modern analytics and patient tracking\n\n{$email->notes}\n\n📞 WhatsApp: +91-9453619260\n🌐 entry.umakant.online";
                
            case 'hospital_management':
                return "Hello {$clientName}! 🏥\n\nComplete Hospital Management System proposal:\n\n💰 Investment: ₹{$email->estimated_cost}\n⏰ Timeline: {$email->timeframe}\n\n🏥 Full hospital operations management\n👥 Patient records & staff management\n💊 Inventory & billing systems\n\n{$email->notes}\n\n📞 WhatsApp: +91-9453619260\n🌐 entry.umakant.online";
                
            default:
                return "Hello {$clientName}! 👋\n\nThank you for your inquiry about {$email->project_name}.\n\n💰 Estimated Cost: ₹{$email->estimated_cost}\n⏰ Timeframe: {$email->timeframe}\n\n📝 {$email->notes}\n\nLet's discuss your requirements in detail!\n\n📞 WhatsApp: +91-9453619260\n🌐 entry.umakant.online";
        }
    }
}
