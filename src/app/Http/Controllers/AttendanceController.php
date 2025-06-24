<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use App\Models\WorkBreak;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index($month = null)
    {
        $currentDateObj = $month ? Carbon::createFromFormat('Y-m', $month) : Carbon::now();
        $currentDate = $currentDateObj->format('Y/m');

        $prevMonth = $currentDateObj->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentDateObj->copy()->addMonth()->format('Y-m');

        $firstDay = $currentDateObj->copy()->startOfMonth();
        $finalDay = $currentDateObj->copy()->endOfMonth();

        $attendances = Attendance::with('workBreaks')
            ->where('user_id', auth()->id())
            ->whereBetween('work_date', [$firstDay, $finalDay])
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->work_date)->format('Y-m-d');
            });

        $weekday = ['日', '月', '火', '水', '木', '金', '土'];
        $dailyData = [];
        $period = CarbonPeriod::create($firstDay, $finalDay);
        foreach ($period as $date) {
            $dateKey = $date->format('Y-m-d');
            $formattedDate = $date->format('m/d') . '(' . $weekday[$date->dayOfWeek] . ')';

            if (isset($attendances[$dateKey])) {
                $attendance = $attendances[$dateKey];
                $attendance->formatted_date = $formattedDate;

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

                $dailyData[] = $attendance;
            } else {
                $dailyData[] = (object) [
                    'formatted_date' => $formattedDate,
                    'clock_in' => null,
                    'clock_out' => null,
                    'break_time' => '',
                    'work_time' => '',
                ];
            }
        }

        return view('list', compact(
            'currentDate',
            'prevMonth',
            'nextMonth',
            'dailyData'
        ));
    }
}
