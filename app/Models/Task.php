<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Task extends Model
{
    use HasFactory;
    protected $fillable = [
        'status',
        'title',
        'priority',
        'description',
        'parent_id'
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::updating(function ($task) {
            if ($task->isDirty('status') && $task->status === 'completed') {
                $task->completedAt = Carbon::now();
            }
        });
    }

    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Task::class, 'parent_id');
    }

    public static function checkUserTask(Task $task): bool
    {
        return auth()->user()->id !== $task->user_id;
    }

    public static function getTaskWithChildren(Task $task): Task
    {
        $task->load('children');
        foreach ($task->children as $child) {
            self::getTaskWithChildren($child);
        }
        return $task;
    }
}
