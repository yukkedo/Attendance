<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        $currentDateObj = $month ? Carbon::createFromFormat('Y-m', $month) : Carbon::now();
        $currentDate = $currentDateObj->format('Y/m'); //表示の変更

        $prevMonth = $currentDateObj->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentDateObj->copy()->addMonth()->format('Y-m');

        $firstDay = $currentDateObj->copy()->startOfMonth();
        $finalDay = $currentDateObj->copy()->endOfMonth();

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

    public function export($userId, $month = null)
    {
        $user = User::find($userId);
        $currentDateObj = $month ? Carbon::createFromFormat('Y-m', $month) : Carbon::now();
        $firstDay = $currentDateObj->copy()->startOfMonth();
        $finalDay = $currentDateObj->copy()->endOfMonth();

        $attendances = Attendance::with('workBreaks')
            ->where('user_id', $userId)
            ->whereBetween('work_date', [$firstDay, $finalDay])
            ->get();

        $weekday = ['日', '月', '火', '水', '木', '金', '土'];
        $csvHeader = ['日付', '曜日', '出勤時間', '退勤時間', '休憩時間', '勤務時間'];

        $csvData = [];
        foreach($attendances as $attendance) {
            $date = Carbon::parse($attendance->work_date);
            $formattedDate = $date->format('Y/m/d');
            $dayOfWeek = $weekday[$date->dayOfWeek];

            $totalBreak = 0;
            foreach ($attendance->workBreaks as $break) {
                if ($break->break_start && $break->break_end) {
                    $start = Carbon::parse($break->break_start);
                    $end = Carbon::parse($break->break_end);
                    $totalBreak += $end->diffInMinutes($start);
                }
            }  
            $breakTime = $totalBreak > 0 ? sprintf('%d:%02d', floor($totalBreak / 60), $totalBreak % 60) : '';

            $workTime = '';
            if ($attendance->clock_in && $attendance->clock_out) {
                $start = Carbon::parse($attendance->clock_in);
                $end = Carbon::parse($attendance->clock_out);
                $workMinutes = $end->diffInMinutes($start) - $totalBreak;
                $workTime = sprintf('%d:%02d', floor($workMinutes / 60), $workMinutes % 60);
            }

            $csvData[] = [
                $formattedDate,
                $dayOfWeek,
                $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : '',
                $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '',
                $breakTime,
                $workTime,

            ];
        }

        $response = new StreamedResponse(function () use ($csvHeader, $csvData) {
            $file = fopen('php://output', 'w');
            mb_convert_variables('SJIS-win', 'UTF-8', $csvHeader);
            fputcsv($file, $csvHeader);
            foreach ($csvData as $row) {
                mb_convert_variables('SJIS-win', 'UTF-8', $row);
                fputcsv($file, $row);
            }
            fclose($file);
        });

        $fileName = 'attendance_' . $user->name . '_' . $currentDateObj->format('Y_m') . '.csv';
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }
}
