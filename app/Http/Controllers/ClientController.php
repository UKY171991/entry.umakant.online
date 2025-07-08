<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $draw = $request->get('draw');
            $start = $request->get('start', 0);
            $length = $request->get('length', 10);
            $search_value = '';
            $orderColumnIndex = 0;
            $orderDirection = 'desc';
            
            // Safely get search value
            $search = $request->get('search');
            if (is_array($search) && isset($search['value'])) {
                $search_value = $search['value'];
            }
            
            // Safely get order information
            $order = $request->get('order');
            if (is_array($order) && isset($order[0]) && isset($order[0]['column'])) {
                $orderColumnIndex = $order[0]['column'];
                $orderDirection = $order[0]['dir'] ?? 'desc';
            }
            
            $columns = $request->get('columns', []);

            $query = Client::query();

            // Apply search filter
            if (!empty($search_value)) {
                $query->where(function($q) use ($search_value) {
                    $q->where('name', 'like', '%' . $search_value . '%')
                      ->orWhere('email', 'like', '%' . $search_value . '%')
                      ->orWhere('phone', 'like', '%' . $search_value . '%');
                });
            }

            // Default ordering by created_at in descending order
            $query->orderBy('created_at', 'desc');

            // Apply ordering from DataTable if provided
            if (!empty($columns) && isset($columns[$orderColumnIndex]['data'])) {
                $orderColumn = $columns[$orderColumnIndex]['data'];
                if (in_array($orderColumn, ['id', 'name', 'email', 'phone', 'created_at'])) {
                    $query->orderBy($orderColumn, $orderDirection);
                }
            }

            $totalRecords = Client::count();
            $filteredRecords = $query->count();
            $clients = $query->offset($start)->limit($length)->get();

            $data = [];
            $i = $start;
            foreach ($clients as $client) {
                $data[] = [
                    'sr_no' => ++$i,
                    'id' => $client->id,
                    'name' => $client->name,
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'address' => $client->address ?? 'N/A',
                    'action' => '<div class="btn-group" role="group">
                        <button type="button" class="btn btn-info btn-sm editClient" data-id="'.$client->id.'" title="Edit Client">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm deleteClient" data-id="'.$client->id.'" title="Delete Client">
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

        return view('clients.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Return redirect to index for modal-based creation
        return redirect()->route('clients.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email' . ($request->client_id ? ',' . $request->client_id : ''),
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $client = Client::updateOrCreate(
            ['id' => $request->client_id],
            [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
            ]
        );

        return response()->json(['success' => 'Client saved successfully!', 'client' => $client]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $client = Client::find($id);
        if (!$client) {
            return response()->json(['error' => 'Client not found'], 404);
        }
        return response()->json($client);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email,' . $client->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $client->update($request->all());

        return response()->json(['success' => 'Client updated successfully!', 'client' => $client]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        $client->delete();

        return response()->json(['success' => 'Client deleted successfully!']);
    }

    /**
     * Get clients for dropdown
     */
    public function getClientsForDropdown()
    {
        $clients = Client::select('id', 'name')->orderBy('name')->get();
        return response()->json($clients);
    }
}
