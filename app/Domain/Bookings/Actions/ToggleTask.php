<?php

namespace App\Domain\Bookings\Actions;

use App\Domain\Bookings\Models\BookingTask;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ToggleTask
{
    public function __invoke(BookingTask $task, User $actor): BookingTask
    {
        return DB::transaction(function () use ($task, $actor) {
            $task = BookingTask::whereKey($task->id)->lockForUpdate()->first();

            if ($task->status === 'done') {
                $task->status = 'open';
                $task->completed_at = null;
                $task->completed_by_user_id = null;
            } else {
                $task->status = 'done';
                $task->completed_at = now();
                $task->completed_by_user_id = $actor->id;
            }

            $task->save();

            return $task;
        });
    }
}
