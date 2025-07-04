<?php

namespace App\Http\Controllers;

use App\Models\PendingTask;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class PendingTaskController extends Controller
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

            $query = PendingTask::with('client');

            // Apply search filter
            if (!empty($search_value)) {
                $query->where('task_name', 'like', '%' . $search_value . '%')
                      ->orWhereHas('client', function($q) use ($search_value) {
                          $q->where('name', 'like', '%' . $search_value . '%');
                      })
                      ->orWhere('description', 'like', '%' . $search_value . '%')
                      ->orWhere('status', 'like', '%' . $search_value . '%');
            }

            $query->orderBy('created_at', 'desc');

            $totalRecords = PendingTask::count();
            $filteredRecords = $query->count();
            $tasks = $query->offset($start)->limit($length)->get();

            $data = [];
            $i = $start;
            foreach ($tasks as $task) {
                $statusBadge = '';
                switch($task->status) {
                    case 'Completed':
                        $statusBadge = '<span class="badge bg-success">Completed</span>';
                        break;
                    case 'Pending':
                        $statusBadge = '<span class="badge bg-warning">Pending</span>';
                        break;
                    case 'In Progress':
                        $statusBadge = '<span class="badge bg-info">In Progress</span>';
                        break;
                }

                $paymentBadge = $task->payment_status == 'Paid' ? 
                    '<span class="badge bg-success">Paid</span>' : 
                    '<span class="badge bg-danger">Unpaid</span>';

                $imageHtml = $task->image_path ? 
                    '<img src="' . asset('storage/' . $task->image_path) . '" alt="Task Image" style="width: 50px; height: 50px; object-fit: cover;">' : 
                    '<span class="text-muted">No Image</span>';

                $data[] = [
                    'sr_no' => ++$i,
                    'image' => $imageHtml,
                    'task_name' => $task->task_name,
                    'client_name' => $task->client ? $task->client->name : 'N/A',
                    'description' => substr($task->description, 0, 50) . '...',
                    'due_date' => $task->due_date->format('Y-m-d'),
                    'status' => $statusBadge,
                    'payment' => '₹' . number_format($task->payment, 2),
                    'payment_status' => $paymentBadge,
                    'action' => '<button type="button" class="btn btn-info btn-sm editTask" data-id="'.$task->id.'"><i class="fas fa-edit"></i> Edit</button> <button type="button" class="btn btn-danger btn-sm deleteTask" data-id="'.$task->id.'"><i class="fas fa-trash"></i> Delete</button>'
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
        return view('pending-tasks.index', compact('clients'));
    }

    public function create()
    {
        // Return redirect to index for modal-based creation
        return redirect()->route('pending-tasks.index');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'task_name' => 'required|string|max:255',
            'client_id' => 'required|exists:clients,id',
            'description' => 'required|string',
            'due_date' => 'required|date',
            'status' => 'required|in:Pending,Completed,In Progress',
            'payment' => 'nullable|numeric|min:0',
            'payment_status' => 'required|in:Paid,Unpaid',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('task-images', 'public');
        }

        $task = PendingTask::updateOrCreate(
            ['id' => $request->task_id],
            [
                'task_name' => $request->task_name,
                'client_id' => $request->client_id,
                'description' => $request->description,
                'due_date' => $request->due_date,
                'status' => $request->status,
                'payment' => $request->payment ?? 0,
                'payment_status' => $request->payment_status,
                'image_path' => $imagePath ?? $request->existing_image_path,
            ]
        );

        return response()->json(['success' => 'Task saved successfully!', 'task' => $task]);
    }

    public function show(string $id)
    {
        $task = PendingTask::find($id);
        if (!$task) {
            return response()->json(['error' => 'Task not found'], 404);
        }
        if ($task) {
            $task->payment = (float) $task->payment; // Ensure payment is a float
            $task->due_date = $task->due_date ? \Illuminate\Support\Carbon::parse($task->due_date)->format('Y-m-d') : null;
        }
        return response()->json($task);
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'task_name' => 'required|string|max:255',
            'client_id' => 'required|exists:clients,id',
            'description' => 'required|string',
            'due_date' => 'required|date',
            'status' => 'required|in:Pending,In Progress,Completed',
            'payment' => 'nullable|numeric|min:0',
            'payment_status' => 'required|in:Unpaid,Paid',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $task = PendingTask::find($id);
        if (!$task) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        $imagePath = $task->image_path;
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($imagePath && Storage::exists('public/' . $imagePath)) {
                Storage::delete('public/' . $imagePath);
            }
            
            $imagePath = $request->file('image')->store('task-images', 'public');
        }

        $task->update([
            'task_name' => $request->task_name,
            'client_id' => $request->client_id,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'status' => $request->status,
            'payment' => $request->payment,
            'payment_status' => $request->payment_status,
            'image_path' => $imagePath,
        ]);

        return response()->json(['success' => 'Task updated successfully!', 'task' => $task]);
    }

    public function edit(string $id)
    {
        $task = PendingTask::find($id);
        if (!$task) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        // Format due_date to Y-m-d for the HTML date input
        $task->due_date = $task->due_date ? \Illuminate\Support\Carbon::parse($task->due_date)->format('Y-m-d') : null;
        // Ensure payment is a float
        $task->payment = (float) $task->payment;

        return response()->json($task);
    }

    public function destroy(string $id)
    {
        $task = PendingTask::find($id);
        if ($task && $task->image_path) {
            \Storage::disk('public')->delete($task->image_path);
        }
        $task->delete();
        return response()->json(['success' => 'Task deleted successfully!']);
    }
}
