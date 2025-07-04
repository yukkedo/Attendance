<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\WorkBreak;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimeClockController extends Controller
{
    public function index()
    {
        $now = Carbon::now()->locale('ja')->timezone('Asia/Tokyo');

        $weekdayMap = ['日', '月', '火', '水', '木', '金', '土'];
        $weekday = $weekdayMap[$now->dayOfWeek];

        $today = $now->toDateString();

        $user = Auth::user();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();

        $status = '勤務外';
        $onBreak = false;

        if($attendance) {
            if(is_null($attendance->clock_out)) {
                $latestBreak = $attendance->workBreaks()
                    ->whereNull('break_end') 
                    ->latest('break_start')
                    ->first(); 
                if($latestBreak) {
                    $status = '休憩中';
                    $onBreak = true;
                }else {
                    $status = '出勤中';
                }
            } else {
                $status = '退勤済';
            }
        }

        $clockIn = is_null($attendance);  
        $onDuty = $attendance && is_null($attendance->clock_out);  
        $clockOut = $onDuty && !$onBreak;  
        $breakStart = $onDuty && !$onBreak;  
        $breakEnd = $onBreak;  
        $clockOutMessage = $attendance && !is_null($attendance->clock_out); 

        return view('registration', compact(
            'now',
            'weekday',
            'status',
            'clockIn',
            'clockOut',
            'breakStart',
            'breakEnd',
            'clockOutMessage'
        ));
    }
    
    public function clockIn()
    {
        $user = Auth::user();
        $today = now()->toDateString(); 

        $alreadyClockedIn = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->exists();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $today,
            'clock_in' => now()->format('H:i')
        ]);

        return redirect('/attendance');
    }

    public function clockOut()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();

        if($attendance && is_null($attendance->clock_out))
        {
            $attendance->update([
                'clock_out' => now()->format('H:i')
            ]);
        }

        return redirect('/attendance');
    }

    public function breakStart()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();
            
        if($attendance) {
            $onBreak = $attendance->workBreaks()
                ->whereNull('break_end')
                ->first();
            if(!$onBreak) {
                $attendance->workBreaks()->create([
                    'break_start' =>now()->format('H:i')
                ]);
            }    
        }
        return redirect('/attendance');
    }

    public function breakEnd()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();

        if($attendance) {
            $onBreak =$attendance->workBreaks()
                ->whereNull('break_end')
                ->latest('break_start')
                ->first();

            if($onBreak) {
                $onBreak->update([
                    'break_end' => now()->format('H:i')
                ]);
            }
        }
        return redirect('/attendance');
    }
}