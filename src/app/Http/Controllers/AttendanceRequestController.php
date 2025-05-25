<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceRequestController extends Controller
{
    public function getDetail($id)
    {
        $user = Auth::user();
        $attendance = Attendance::find($id);
        
        // 出勤日の表示変更
        $workDate = Carbon::parse($attendance->work_date);
        $year = $workDate->format('Y年');
        $date = $workDate->format('n月d日');

        // 出勤・退勤時間の表示
        $clockIn = $attendance->clock_in 
            ? Carbon::parse($attendance->clock_in)->format('H:i') 
            : '';
        $clockOut = $attendance->clock_out
            ? Carbon::parse($attendance->clock_out)->format('H:i')
            : '';

        // 休憩時間の表示
        $breaks = optional($attendance->workBreaks)->map(function ($break){
            return [
                'start' => $break->break_start
                    ? Carbon::parse($break->break_start)->format('H:i') : '',
                'end' => $break->break_end
                    ? Carbon::parse($break->break_end)->format('H:i') : '',
            ];
        })->toArray() ?? [];
        $breaks[] = ['start' => '', 'end' => ''];

        return view('detail', compact(
            'user',
            'attendance',
            'year',
            'date',
            'clockIn',
            'clockOut',
            'breaks'
        ));
    }
}