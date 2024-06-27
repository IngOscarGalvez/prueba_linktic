<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index()
    {
        return Task::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:pending,in_progress,completed',
            'due_date' => 'required|date',
        ]);

        $task = Task::create($request->all());

        return response()->json($task, 201);
    }

    public function show($id)
    {
        return Task::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $request->validate([
            'title' => 'string|max:255',
            'description' => 'string',
            'status' => 'in:pending,in_progress,completed',
            'due_date' => 'date',
        ]);

        $task->update($request->all());

        return response()->json($task, 200);
    }

    public function destroy($id)
    {
        Task::findOrFail($id)->delete();

        return response()->json(null, 204);
    }
}
