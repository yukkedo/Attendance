<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminAttendanceController extends Controller
{
    public function index($date = null)
    {
        $targetDate = $date ? Carbon::createFromFormat('Y-m-d', $date) : Carbon::today();

        $prevDate = $targetDate->copy()->subDay()->format('Y-m-d');
        $nextDate = $targetDate->copy()->addDay()->format('Y-m-d');

        $attendances = Attendance::with(['user', 'workBreaks'])
            ->whereDate('work_date', $targetDate)
            ->get();

        foreach ($attendances as $attendance) {
            $attendance->user->name = $attendance->user->name ?? '';

            $totalBreak = 0;
            foreach ($attendance->workBreaks as $break) {
                if ($break->break_start && $break->break_end) {
                    $start = Carbon::parse($break->break_start);
                    $end = Carbon::parse($break->break_end);
                    $totalBreak += $end->diffInMinutes($start);
                }
            }

            if ($totalBreak > 0) {
                $hours = floor($totalBreak / 60);
                $minutes = $totalBreak % 60;
                $attendance->break_time = sprintf('%d:%02d', $hours, $minutes);
            } else {
                $attendance->break_time = '';
            }

            if ($attendance->clock_in && $attendance->clock_out) {
                $start = Carbon::parse($attendance->clock_in);
                $end = Carbon::parse($attendance->clock_out);
                $workMinutes = $end->diffInMinutes($start) - $totalBreak;

                $hours = floor($workMinutes / 60);
                $minutes = $workMinutes % 60;
                $attendance->work_time = sprintf('%d:%02d', $hours, $minutes);
            } else {
                $attendance->work_time = '';
            }
        }

        return view('admin.list', compact(
            'targetDate',
            'prevDate',
            'nextDate',
            'attendances'
        ));
    }
}
