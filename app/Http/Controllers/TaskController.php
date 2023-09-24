<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): array
    {
        $tasks = Task::where('user_id', '=', auth()->user()->id)->get();

        // Pre-load related child tasks for each task
        $tasks->load('children');

        // Convert a collection of tasks to an array of resources
        $taskResources = $tasks->transform(function ($task) {
            return new TaskResource($task);
        });

        return $taskResources->toArray();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TaskRequest $request, $parentId = null): TaskResource
    {

        $task = new Task($request->all());
        $task->user_id = auth()->user()->id;

        if ($parentId) {
            $parentTask = Task::find($parentId);
            if ($parentTask) {
                $task->parent_id = $parentTask->id;
            }
        }

        $task->save();

        return new TaskResource($task);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task): TaskResource
    {
        return new TaskResource(Task::getTaskWithChildren($task));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TaskRequest $request, string $id): TaskResource|\Illuminate\Http\JsonResponse
    {
        $task = Task::findOrFail($id);

        if (Task::checkUserTask($task)) {
            return response()->json(['error' => 'Not your task!'], 400);
        }

        $task->fill($request->all());
        $task->save();

        return new TaskResource($task);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $task = Task::find($id);

        if(Task::checkUserTask($task)){
            return response()->json(['error' => 'Not your task!'], 400);
        }

        $task->delete();

        return new TaskResource($task);
    }
}
