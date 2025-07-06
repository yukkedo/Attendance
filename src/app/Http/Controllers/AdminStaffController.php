<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
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
        $currentDate = $currentDateObj->format('Y/m'); 

        $prevMonth = $currentDateObj->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentDateObj->copy()->addMonth()->format('Y-m');

        $firstDay = $currentDateObj->copy()->startOfMonth();
        $finalDay = $currentDateObj->copy()->endOfMonth();

        $attendances = Attendance::with('workBreaks')
            ->where('user_id', $userId)
            ->whereBetween('work_date', [$firstDay, $finalDay])
            ->get()
            ->keyBy(function ($item) {
                return  Carbon::parse($item->work_date)->format('Y-m-d');
            });

        $weekday = ['日', '月', '火', '水', '木', '金', '土'];
        $dailyDate = [];
        $period = CarbonPeriod::create($firstDay, $finalDay);  

        foreach ($period as $date) {
            $dateKey = $date->format('Y-m-d');
            $formattedDate = $date->format('m/d') . '('. $weekday[$date->dayOfWeek] . ')';

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

                $attendance->break_time = $totalBreak > 0 
                    ? sprintf('%d:%02d', floor($totalBreak / 60), $totalBreak % 60) : '';

                if ($attendance->clock_in && $attendance->clock_out) {
                    $start = Carbon::parse($attendance->clock_in);
                    $end = Carbon::parse($attendance->clock_out);
                    $workMinutes = $end->diffInMinutes($start) - $totalBreak;

                    $attendance->work_time = sprintf('%d:%02d', floor($workMinutes / 60), $workMinutes % 60);
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

        return view('admin.staff_attendance', compact(
            'user',
            'dailyData',
            'currentDate',
            'prevMonth',
            'nextMonth',
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
