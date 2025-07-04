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


            $data = Expense::orderBy('created_at', 'desc')->select('*');

             try {
                 return DataTables::of($data)
                     ->addIndexColumn()
                     ->addColumn('amount', function($row) {
                         return '₹' . number_format($row->amount, 2);
                     })
                     ->addColumn('action', function($row){
                         $editBtn = '<a href="javascript:void(0)" data-toggle="tooltip" data-id="' . $row->id . '" data-original-title="Edit" class="edit btn btn-primary btn-sm editExpense">Edit</a>';
                         $deleteBtn = '<a href="javascript:void(0)" data-toggle="tooltip" data-id="' . $row->id . '" data-original-title="Delete" class="btn btn-danger btn-sm deleteExpense">Delete</a>';
                         return $editBtn . ' ' . $deleteBtn;
                     })
                     ->rawColumns(['action'])
                     ->make(true);
             } catch (\Exception $e) {
                 return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
             }
        }

        return view('expenses.index_new');
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
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Expense::find($id)->delete();

        return response()->json(['success' => 'Expense deleted successfully.']);
    }
}
