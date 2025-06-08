<?php

namespace App\Http\Controllers;

use App\Http\Requests\DetailRequest;
use App\Models\Attendance;
use App\Models\Attendance_change;
use App\Models\WorkBreak;
use App\Models\WorkBreak_change;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminAttendanceRequestController extends Controller
{
    public function getDetail($id)
    {
        $attendance = Attendance::find($id);
        $user = $attendance->user;
        $change = $attendance->attendanceChange;
        // 出勤日の表示変更
        $workDate = Carbon::parse($attendance->work_date);
        $year = $workDate->format('Y年');
        $date = $workDate->format('n月j日');

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

        if (!$isPending) {
            $breaks[] = ['start' => '', 'end' => ''];
        }

        $remarks = $isPending ? $change->remarks : '';

        return view('admin.detail', compact(
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
                'user_id' => $request->user_id,
                'attendance_id' => $request->attendance_id,
                'new_clock_in' => $request->new_clock_in,
                'new_clock_out' => $request->new_clock_out,
                'remarks' => $request->remarks,
                'status' => 'approved',
                'admin_id' => Auth::guard('admin')->id(),
            ]);
            $attendance = Attendance::find($request->attendance_id);
            $attendance->clock_in = $request->new_clock_in;
            $attendance->clock_out = $request->new_clock_out;
            $attendance->save();

            // 全ての休憩時間を登録
            foreach ($request->breaks as $index => $break) {
                if (empty($break['start']) && empty($break['end'])) {
                    continue;
                }
                
                $workBreakId = $request->work_break_id[$index] ?? null;
                WorkBreak_change::create([
                    'work_break_id' => $workBreakId,
                    'attendance_change_id' => $attendanceChange->id,
                    'new_break_start' => $break['start'],
                    'new_break_end' => $break['end'],
                    'status' => 'approved',
                    'admin_id' => Auth::guard('admin')->id(),
                ]);

                if ($workBreakId) {
                    $workBreak = WorkBreak::find($workBreakId);
                    if ($workBreak) {
                        $workBreak->break_start = $break['start'];
                        $workBreak->break_end = $break['end'];
                        $workBreak->save();
                    }
                }
            }

            DB::commit();
            return back()->with('success', '送信しました');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '失敗しました');
        }
    }

    public function viewApproved(Attendance_change $attendance_correct_request)
    {
        $attendanceChange = $attendance_correct_request->load(['user', 'attendance', 'workBreakChanges']);

        $date = \Carbon\Carbon::parse($attendanceChange->attendance->date);

        return view('admin.approval', compact('attendanceChange', 'date'));
    }

    public function approve(Attendance_change $attendance_correct_request)
    {
        DB::transaction(function () use ($attendance_correct_request) {
            $attendance = $attendance_correct_request->attendance;
            
            $attendance->update([
                'clock_in' => $attendance_correct_request->new_clock_in,
                'clock_out' => $attendance_correct_request->new_clock_out,
            ]);
            
            WorkBreak::where('attendance_id', $attendance->id)->delete();

            foreach ($attendance_correct_request->workBreakChanges as $change) {
                WorkBreak::create([
                    'attendance_id' => $attendance->id,
                    'break_start' => $change->new_break_start,
                    'break_end' => $change->new_break_end,
                ]);
            }

            $attendance_correct_request->update([
                'status' => 'approved',
                'admin_id' => auth('admin')->id(),
            ]);

            foreach($attendance_correct_request->workBreakChanges as $change) {
                $change->update([
                    'status' => 'approved',
                    'admin_id' => auth('admin')->id(),
                ]);
            }
        });
        return redirect('/stamp_correction_request/list');
    }
}
