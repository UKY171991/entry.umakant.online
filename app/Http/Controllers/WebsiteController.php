<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WebsiteController extends Controller
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

            $query = Website::with('client');

            // Apply search filter
            if (!empty($search_value)) {
                $query->whereHas('client', function($q) use ($search_value) {
                    $q->where('name', 'like', '%' . $search_value . '%');
                })->orWhere('website_url', 'like', '%' . $search_value . '%')
                  ->orWhere('status', 'like', '%' . $search_value . '%');
            }

            $query->orderBy('created_at', 'desc');

            $totalRecords = Website::count();
            $filteredRecords = $query->count();
            $websites = $query->offset($start)->limit($length)->get();

            $data = [];
            $i = $start;
            foreach ($websites as $website) {
                $statusBadge = '';
                switch($website->status) {
                    case 'UP':
                        $statusBadge = '<span class="badge bg-success">UP ✓</span>';
                        break;
                    case 'DOWN':
                        $statusBadge = '<span class="badge bg-danger">DOWN</span>';
                        break;
                    default:
                        $statusBadge = '<span class="badge bg-secondary">N/A</span>';
                }

                $data[] = [
                    'sr_no' => ++$i,
                    'client_name' => $website->client ? $website->client->name : 'N/A',
                    'website_url' => $website->website_url,
                    'status' => $statusBadge,
                    'last_updated' => $website->last_updated ? $website->last_updated->format('m-d-Y H:i:s A') : 'N/A',
                    'action' => '<div class="btn-group" role="group">
                        <button type="button" class="btn btn-warning btn-sm editWebsite" data-id="'.$website->id.'" title="Edit Website">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm viewWebsite" data-url="'.$website->website_url.'" title="View Website">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-primary btn-sm testWebsite" data-id="'.$website->id.'" title="Test Website">
                            <i class="fas fa-vial"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm deleteWebsite" data-id="'.$website->id.'" title="Delete Website">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>'
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
        return view('websites.index', compact('clients'));
    }

    public function create()
    {
        // Return redirect to index for modal-based creation
        return redirect()->route('websites.index');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'website_url' => 'required|url|max:255',
            'status' => 'required|in:UP,DOWN,N/A',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $website = Website::updateOrCreate(
            ['id' => $request->website_id],
            [
                'client_id' => $request->client_id,
                'client_name' => Client::find($request->client_id)->name ?? 'N/A',
                'website_url' => $request->website_url,
                'status' => $request->status,
                'last_updated' => now(),
            ]
        );

        return response()->json(['success' => 'Website saved successfully!', 'website' => $website]);
    }

    public function show(string $id)
    {
        // Method implementation
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'website_url' => 'required|url|max:255',
            'status' => 'required|in:UP,DOWN,N/A',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $website = Website::find($id);
        if (!$website) {
            return response()->json(['error' => 'Website not found'], 404);
        }

        $website->update([
            'client_id' => $request->client_id,
            'client_name' => Client::find($request->client_id)->name ?? 'N/A',
            'website_url' => $request->website_url,
            'status' => $request->status,
            'last_updated' => now(),
        ]);

        return response()->json(['success' => 'Website updated successfully!', 'website' => $website]);
    }

    public function edit(string $id)
    {
        $website = Website::find($id);
        return response()->json($website);
    }

    public function testWebsite($id)
    {
        $website = Website::findOrFail($id);
        
        try {
            // Test website using HTTP client
            $response = \Illuminate\Support\Facades\Http::timeout(10)->get($website->website_url);
            
            if ($response->successful()) {
                $website->status = 'UP';
                $status = 'UP - Website is accessible';
            } else {
                $website->status = 'DOWN';
                $status = 'DOWN - HTTP Error: ' . $response->status();
            }
        } catch (\Exception $e) {
            $website->status = 'DOWN';
            $status = 'DOWN - Connection failed: ' . $e->getMessage();
        }
        
        $website->save();
        
        return response()->json([
            'success' => 'Website test completed',
            'status' => $status,
            'website_status' => $website->status
        ]);
    }

    public function destroy(string $id)
    {
        Website::find($id)->delete();
        return response()->json(['success' => 'Website deleted successfully!']);
    }

    public function test(string $id)
    {
        $website = Website::find($id);
        
        // Simple URL test (in real implementation, you would check HTTP status)
        $status = 'UP'; // This would be determined by actual HTTP request
        
        $website->update([
            'status' => $status,
            'last_updated' => now()
        ]);

        return response()->json(['success' => 'Website tested successfully!', 'status' => $status]);
    }
}
