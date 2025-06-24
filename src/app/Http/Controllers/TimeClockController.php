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

        // 出勤データが存在するか
        if($attendance) {
            // まだ退勤していないか(出勤中or休憩中)
            if(is_null($attendance->clock_out)) {
                // 休憩中かどうかを確認
                $latestBreak = $attendance->workBreaks()
                    ->whereNull('break_end') //休憩戻りが未登録
                    ->latest('break_start')
                    ->first(); //最新の休憩レコードを取得
                if($latestBreak) {
                    // latestBreakが見つかれば休憩中、なければ出勤中
                    $status = '休憩中';
                    $onBreak = true;
                }else {
                    $status = '出勤中';
                }
            } else {
                $status = '退勤済';
            }
        }

        $clockIn = is_null($attendance);  // 出勤前
        $onDuty = $attendance && is_null($attendance->clock_out);  // 出勤中
        $clockOut = $onDuty && !$onBreak;  // 出勤済で退勤ボタンの表示
        $breakStart = $onDuty && !$onBreak;  // 出勤済で休憩入りのボタンを表示
        $breakEnd = $onBreak;  // 休憩戻りのボタンを表示
        $clockOutMessage = $attendance && !is_null($attendance->clock_out);  // 退勤後のメッセージを表示

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
        $today = now()->toDateString(); // 打刻の日付を取得

        // 打刻の有無を確認
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

        // 今日の出勤記録を取得
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