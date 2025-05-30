<?php

namespace App\Http\Controllers;

use App\Http\Requests\DetailRequest;
use App\Models\Attendance;
use App\Models\Attendance_change;
use App\Models\WorkBreak_change;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceRequestController extends Controller
{
    public function getDetail($id)
    {
        $user = Auth::user();
        $attendance = Attendance::find($id);
        $change = $attendance->attendanceChange;
        // 出勤日の表示変更
        $workDate = Carbon::parse($attendance->work_date);
        $year = $workDate->format('Y年');
        $date = $workDate->format('n月d日');

        // 申請状況の判定
        $isPending = $change !== null && $change->status === 'pending';

        // 出勤・退勤時間の表示
        if ($isPending) {
            $clockIn = $change->new_clock_in ?? '';
            $clockOut = $change->new_clock_out ?? '';
        } else {
            $clockIn = $attendance->clock_in
                ? Carbon::parse($attendance->clock_in)->format('H:i')
                : '';
            $clockOut = $attendance->clock_out
                ? Carbon::parse($attendance->clock_out)->format('H:i')
                : '';
        }
        // 休憩時間の表示
        if ($isPending && $change) {
            $breaks = collect($change->workBreakChanges)->map(function ($break) {
                return [
                    'id' => $break->id,
                    'start' => $break->new_break_start
                        ? Carbon::parse($break->new_break_start)->format('H:i') : '',
                    'end' => $break->new_break_end
                        ? Carbon::parse($break->new_break_end)->format('H:i') : '',
                ];
            })->toArray() ?? [];
        } else {
            $breaks = collect($attendance->workBreaks)->map(function ($break) {
                return [
                    'id' => $break->id,
                    'start' => $break->break_start
                        ? Carbon::parse($break->break_start)->format('H:i') : '',
                    'end' => $break->break_end
                        ? Carbon::parse($break->break_end)->format('H:i') : '',
                ];
            })->toArray() ?? [];
        }

        if(!$isPending) {
            $breaks[] = ['start' => '', 'end' => ''];
        }
        

        $remarks = $isPending ? $change->remarks : '';

        return view('detail', compact(
            'user',
            'attendance',
            'year',
            'date',
            'clockIn',
            'clockOut',
            'breaks',
            'remarks',
            'isPending'
        ));
    }

    public function requestChange(DetailRequest $request)
    {
        DB::beginTransaction(); // トランザクションの開始

        try {
            // 出退勤の変更
            $attendanceChange = Attendance_change::create([
                'user_id' => Auth::id(),
                'attendance_id' => $request->attendance_id,
                'new_clock_in' => $request->new_clock_in,
                'new_clock_out' => $request->new_clock_out,
                'remarks' => $request->remarks,
                'status' => 'pending',
                'admin_id' => null,
            ]);

            // 全ての休憩時間を登録
            foreach ($request->breaks as $index => $break) {
                if (empty($break['start']) && empty($break['end'])) {
                    continue;
                }

                WorkBreak_change::create([
                    'work_break_id' => $request->work_break_id[$index] ?? null,
                    'attendance_change_id' => $attendanceChange->id,
                    'new_break_start' => $break['start'],
                    'new_break_end' => $break['end'],
                    'status' => 'pending',
                    'admin_id' => null,
                ]);
            }

            DB::commit();
            return back()->with('success', '送信しました');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '失敗しました');
        }
    }

    public function applyList(Request $request)
    {
        $tab = $request->query('tab','pending');

        // 承認待ち
        if ($tab === 'pending') {
            $changes = Attendance_change::with(['user', 'attendance'])
                ->where('status', 'pending')
                ->get();
        } elseif ($tab === 'approved') {
            $changes = Attendance_change::with(['user', 'attendance'])
                ->where('status', 'approved')
                ->get();
        } else {
            $changes = collect();
        }
        // 承認済み
        return view('application_list', compact(
            'tab',
            'changes'
        ));
    }
}
