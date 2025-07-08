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
            $data = Expense::orderBy('date', 'desc');

            // Apply filters
            if ($request->has('month') && $request->month) {
                $data->whereRaw('strftime("%Y-%m", date) = ?', [$request->month]);
            }

            if ($request->has('year') && $request->year) {
                $data->whereRaw('strftime("%Y", date) = ?', [$request->year]);
            }

            if ($request->has('category') && $request->category) {
                $data->where('category', 'like', '%' . $request->category . '%');
            }

            if ($request->has('expense_name') && $request->expense_name) {
                $data->where('expense_name', 'like', '%' . $request->expense_name . '%');
            }

             try {
                 return DataTables::of($data)
                     ->addIndexColumn()
                     ->addColumn('amount', function($row) {
                         return '<span class="currency-amount currency-negative"><i class="fas fa-rupee-sign rupee-icon"></i>' . number_format($row->amount, 2) . '</span>';
                     })
                     ->addColumn('action', function($row){
                         return '<div class="btn-group" role="group">
                             <button type="button" class="btn btn-info btn-sm editExpense" data-id="'.$row->id.'" title="Edit Expense">
                                 <i class="fas fa-edit"></i>
                             </button>
                             <button type="button" class="btn btn-danger btn-sm deleteExpense" data-id="'.$row->id.'" title="Delete Expense">
                                 <i class="fas fa-trash"></i>
                             </button>
                         </div>';
                     })
                     ->rawColumns(['action', 'amount'])
                     ->make(true);
             } catch (\Exception $e) {
                 return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
             }
        }

        $totalExpenses = Expense::sum('amount');
        return view('expenses.index_new', compact('totalExpenses'));
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
                'date' => $request->date,
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
