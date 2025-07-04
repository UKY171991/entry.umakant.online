<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Income;
use Illuminate\Http\Request;

class IncomeController extends Controller
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

            $query = Income::with('client');

            // Apply search filter
            if (!empty($search_value)) {
                $query->where(function($q) use ($search_value) {
                    $q->where('total_amount', 'like', '%' . $search_value . '%')
                      ->orWhere('pending_amount', 'like', '%' . $search_value . '%')
                      ->orWhere('received_amount', 'like', '%' . $search_value . '%')
                      ->orWhere('date', 'like', '%' . $search_value . '%')
                      ->orWhereHas('client', function($q) use ($search_value) {
                          $q->where('name', 'like', '%' . $search_value . '%');
                      });
                });
            }

            // Apply custom filters
            if ($request->has('month') && $request->month) {
                // Use SQLite compatible date formatting
                $query->whereRaw('strftime("%Y-%m", date) = ?', [$request->month]);
            }

            if ($request->has('year') && $request->year) {
                // Use SQLite compatible date formatting for year
                $query->whereRaw('strftime("%Y", date) = ?', [$request->year]);
            }

            if ($request->has('pending_filter') && $request->pending_filter !== '') {
                if ($request->pending_filter === '0') {
                    $query->where('pending_amount', 0);
                } elseif ($request->pending_filter === '>0') {
                    $query->where('pending_amount', '>', 0);
                }
            }

            if ($request->has('received_filter') && $request->received_filter !== '') {
                if ($request->received_filter === '0') {
                    $query->where('received_amount', 0);
                } elseif ($request->received_filter === '>0') {
                    $query->where('received_amount', '>', 0);
                }
            }

            // Default ordering by created_at in descending order
            $query->orderBy('created_at', 'desc');

            $totalRecords = Income::count();
            $filteredRecords = $query->count();
            $incomes = $query->offset($start)->limit($length)->get();

            $data = [];
            $i = $start;
            foreach ($incomes as $income) {
                $data[] = [
                    'sr_no' => ++$i,
                    'id' => $income->id,
                    'client_name' => $income->client ? $income->client->name : 'N/A',
                    'client_id' => $income->client_id,
                    'total_amount' => '<span class="currency-amount currency-positive"><i class="fas fa-rupee-sign rupee-icon"></i>' . number_format($income->total_amount, 2) . '</span>',
                    'pending_amount' => '<span class="currency-amount currency-warning"><i class="fas fa-rupee-sign rupee-icon"></i>' . number_format($income->pending_amount, 2) . '</span>',
                    'received_amount' => '<span class="currency-amount currency-info"><i class="fas fa-rupee-sign rupee-icon"></i>' . number_format($income->received_amount, 2) . '</span>',
                    'date' => date('m/d/Y', strtotime($income->date)),
                    'action' => '<div class="btn-group" role="group" aria-label="Income Actions">
                        <button type="button" class="btn btn-info btn-sm editIncome" data-id="'.$income->id.'" title="Edit Income">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm deleteIncome" data-id="'.$income->id.'" title="Delete Income">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>'
                ];
            }

            // Calculate totals for filtered records
            $totals = $query->selectRaw('
                SUM(total_amount) as total_amount,
                SUM(pending_amount) as total_pending,
                SUM(received_amount) as total_received
            ')->first();

            return response()->json([
                "draw" => intval($draw),
                "recordsTotal" => intval($totalRecords),
                "recordsFiltered" => intval($filteredRecords),
                "data" => $data,
                "totals" => [
                    'total_amount' => '<span class="currency-amount currency-positive"><i class="fas fa-rupee-sign rupee-icon"></i>' . number_format($totals->total_amount ?? 0, 2) . '</span>',
                    'total_pending' => '<span class="currency-amount currency-warning"><i class="fas fa-rupee-sign rupee-icon"></i>' . number_format($totals->total_pending ?? 0, 2) . '</span>',
                    'total_received' => '<span class="currency-amount currency-info"><i class="fas fa-rupee-sign rupee-icon"></i>' . number_format($totals->total_received ?? 0, 2) . '</span>'
                ]
            ]);
        }

        $clients = Client::all();
        return view('incomes.index', compact('clients'));
    }

    public function create()
    {
        // Return redirect to index for modal-based creation
        return redirect()->route('incomes.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'total_amount' => 'required|numeric|min:0',
            'pending_amount' => 'nullable|numeric|min:0',
            'received_amount' => 'required|numeric|min:0',
            'date' => 'required|date',
        ]);

        Income::updateOrCreate(
            ['id' => $request->income_id],
            [
                'client_id' => $request->client_id,
                'total_amount' => $request->total_amount,
                'pending_amount' => $request->pending_amount ?? 0,
                'received_amount' => $request->received_amount,
                'date' => $request->date,
            ]
        );

        return response()->json(['success' => 'Income saved successfully.']);
    }

    public function show(string $id)
    {
        $income = Income::with('client')->find($id);
        if (!$income) {
            return response()->json(['error' => 'Income not found'], 404);
        }
        return response()->json($income);
    }

    public function edit(string $id)
    {
        $income = Income::find($id);
        return response()->json($income);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'total_amount' => 'required|numeric|min:0',
            'pending_amount' => 'nullable|numeric|min:0',
            'received_amount' => 'nullable|numeric|min:0',
            'date' => 'required|date',
        ]);

        $income = Income::find($id);
        if (!$income) {
            return response()->json(['error' => 'Income not found'], 404);
        }

        $income->update([
            'client_id' => $request->client_id,
            'total_amount' => $request->total_amount,
            'pending_amount' => $request->pending_amount ?? 0,
            'received_amount' => $request->received_amount ?? 0,
            'date' => $request->date,
        ]);

        return response()->json(['success' => 'Income updated successfully!', 'income' => $income]);
    }

    public function destroy(string $id)
    {
        Income::find($id)->delete();
        return response()->json(['success' => 'Income deleted successfully.']);
    }
}


