<?php

namespace App\Http\Controllers;

use App\Models\Email;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\EmailRequest;

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

    public function sendEmail(string $id)
    {
        $emailRecord = Email::find($id);

        if (!$emailRecord) {
            return response()->json(['error' => 'Email record not found'], 404);
        }

        try {
            // For demonstration, we'll just simulate sending an email.
            // In a real application, you would use Laravel's Mail facade here.
            // Example: Mail::to($emailRecord->email)->send(new YourMailableClass());

            // Log the action or send a notification
            
            return response()->json(['success' => 'Test email sent successfully to ' . $emailRecord->email]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to send email: ' . $e->getMessage()], 500);
        }
    }
}
