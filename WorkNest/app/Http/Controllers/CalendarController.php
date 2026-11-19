<?php

namespace App\Http\Controllers;

use App\Models\GroupTask;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(Request $request): View
    {
        // Which month to show? Looks like ?month=2026-11 (defaults to this month)
        $month = (string) $request->query('month', '');

        $current = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)
            ? Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfDay()
            : now()->startOfMonth();

        // The grid starts on the Sunday before the 1st and ends on the Saturday after the last day
        $start = $current->copy()->startOfWeek(Carbon::SUNDAY);
        $end = $current->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);

        // Cut the days into rows of 7
        $weeks = array_chunk(CarbonPeriod::create($start, $end)->toArray(), 7);

        // My own tasks inside the grid, grouped by date
        $tasks = $request->user()->tasks()
            ->whereDate('deadline', '>=', $start->toDateString())
            ->whereDate('deadline', '<=', $end->toDateString())
            ->orderBy('is_done')
            ->get()
            ->groupBy(fn ($task) => $task->deadline->toDateString());

        // Group tasks assigned to me inside the grid, grouped by date
        $groupTasks = GroupTask::query()
            ->where('assigned_to', $request->user()->id)
            ->whereNotNull('deadline')
            ->whereDate('deadline', '>=', $start->toDateString())
            ->whereDate('deadline', '<=', $end->toDateString())
            ->orderByRaw('progress >= 100')
            ->get()
            ->groupBy(fn ($task) => $task->deadline->toDateString());

        return view('calendar', compact('current', 'weeks', 'tasks', 'groupTasks'));
    }
}