<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ExpenseController extends Controller
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
            
            // Safely get search value
            $search = $request->get('search');
            if (is_array($search) && isset($search['value'])) {
                $search_value = $search['value'];
            }
            
            $query = Expense::query();

            // Apply filters
            if ($request->filled('month') && $request->filled('year')) {
                $month = str_pad($request->month, 2, '0', STR_PAD_LEFT);
                $year = $request->year;
                $query->whereRaw('strftime("%Y-%m", date) = ?', ["$year-$month"]);
            } elseif ($request->filled('date_from') && $request->filled('date_to')) {
                $query->whereBetween('date', [$request->date_from, $request->date_to]);
            }

            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            if ($request->filled('expense_name')) {
                $query->where('expense_name', 'like', '%' . $request->expense_name . '%');
            }
            
            // Handle custom search parameter from filter
            if ($request->filled('search')) {
                $query->where('expense_name', 'like', '%' . $request->search . '%');
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Apply search
            if (!empty($search_value)) {
                $query->where(function($q) use ($search_value) {
                    $q->where('expense_name', 'like', '%' . $search_value . '%')
                      ->orWhere('category', 'like', '%' . $search_value . '%')
                      ->orWhere('amount', 'like', '%' . $search_value . '%')
                      ->orWhere('date', 'like', '%' . $search_value . '%');
                });
            }

            // Get totals for filtered records
            $filteredTotal = (clone $query)->sum('amount');
            
            // Apply ordering
            $order = $request->get('order');
            if (!empty($order) && isset($order[0]['column'])) {
                $orderColumn = $order[0]['column'];
                $orderDir = $order[0]['dir'] ?? 'desc';
                $columns = $request->get('columns');
                
                if (isset($columns[$orderColumn]['name']) && !empty($columns[$orderColumn]['name'])) {
                    $query->orderBy($columns[$orderColumn]['name'], $orderDir);
                } else {
                    $query->orderBy('date', 'desc');
                }
            } else {
                $query->orderBy('date', 'desc');
            }

            // Get paginated results
            $totalRecords = Expense::count();
            $filteredRecords = $query->count();
            $expenses = $query->offset($start)->limit($length)->get();

            $data = [];
            $i = $start;
            foreach ($expenses as $expense) {
                // Format status badge
                $statusBadge = '';
                switch ($expense->status) {
                    case 'paid':
                        $statusBadge = '<span class="badge badge-success">Paid</span>';
                        break;
                    case 'pending':
                        $statusBadge = '<span class="badge badge-warning">Pending</span>';
                        break;
                    case 'recurring':
                        $statusBadge = '<span class="badge badge-info">Recurring</span>';
                        break;
                    default:
                        $statusBadge = '<span class="badge badge-secondary">Unknown</span>';
                }
                
                $data[] = [
                    'DT_RowIndex' => ++$i,
                    'id' => $expense->id,
                    'expense_name' => $expense->expense_name,
                    'amount' => '<span class="currency-amount currency-negative"><i class="fas fa-rupee-sign rupee-icon"></i>' . number_format($expense->amount, 2) . '</span>',
                    'category' => $expense->category,
                    'status' => $statusBadge,
                    'date' => date('m/d/Y', strtotime($expense->date)),
                    'action' => '<div class="btn-group" role="group">
                        <button type="button" class="btn btn-info btn-sm editExpense" data-id="'.$expense->id.'" title="Edit Expense">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm deleteExpense" data-id="'.$expense->id.'" title="Delete Expense">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>'
                ];
            }

            try {
                return response()->json([
                    "draw" => intval($draw),
                    "recordsTotal" => intval($totalRecords),
                    "recordsFiltered" => intval($filteredRecords),
                    "data" => $data,
                    "totals" => [
                        "total_amount" => number_format($filteredTotal, 2)
                    ]
                ]);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
            }
        }

        $totalExpenses = Expense::sum('amount');
        // Get unique categories from expenses
        $categories = Expense::select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->filter()
            ->values();
            
        return view('expenses.index_new', [
            'totalExpenses' => $totalExpenses,
            'categories' => $categories,
            'currentYear' => date('Y'),
            'currentMonth' => date('m')
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'expense_name' => 'required',
            'amount' => 'required|numeric',
            'category' => 'required',
            'date' => 'required|date',
        ]);

        Expense::updateOrCreate(
            ['id' => $request->expense_id],
            [
                'expense_name' => $request->expense_name,
                'amount' => $request->amount,
                'category' => $request->category,
                'status' => $request->status ?? 'paid',
                'date' => $request->date,
                'notes' => $request->notes,
            ]
        );

        return response()->json(['success' => 'Expense saved successfully.']);
    }

    /**
     * Display the specified resource.
     */


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $expense = Expense::find($id);
        return response()->json($expense);
    }

    /**
     * Update the specified resource in storage.
     */


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expense $expense)
    {
        return $this->store($request);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Expense::find($id)->delete();

        return response()->json(['success' => 'Expense deleted successfully.']);
    }
}
