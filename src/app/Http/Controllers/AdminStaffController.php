<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminStaffController extends Controller
{
    public function list()
    {
        $users = User::all();

        return view('admin.staff_list', compact('users'));
    }

    public function staffAttendanceList($userId, $month = null)
    {
        $user = User::find($userId);
        // 対象月を取得する
        $currentDateObj = $month ? Carbon::createFromFormat('Y-m', $month) : Carbon::now();
        $currentDate = $currentDateObj->format('Y/m'); //表示の変更

        // 前月・翌月を取得する
        $prevMonth = $currentDateObj->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentDateObj->copy()->addMonth()->format('Y-m');
        // 月初と月末日を取得
        $firstDay = $currentDateObj->copy()->startOfMonth();
        $finalDay = $currentDateObj->copy()->endOfMonth();

        // ログインユーザーの勤怠情報を取得
        $attendances = Attendance::with('workBreaks')
            ->where('user_id', $userId)
            ->whereBetween('work_date', [$firstDay, $finalDay])
            ->get();

        $weekday = ['日', '月', '火', '水', '木', '金', '土'];
        foreach ($attendances as $attendance) {
            $date = Carbon::parse($attendance->work_date);
            $attendance->formatted_date = $date->format('m/d') . '（' . $weekday[$date->dayOfWeek] . '）';

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

        return view('admin.staff_attendance', compact(
            'user',
            'currentDate',
            'prevMonth',
            'nextMonth',
            'attendances'
        ));
    }
}
