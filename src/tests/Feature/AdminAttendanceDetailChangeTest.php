<?php

namespace Tests\Feature;

use App\Models\Attendance;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminAttendanceDetailChangeTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_attendance_detail_data()
    {
        $admin = \App\Models\Admin::where('email', 'admin@gmail.com')->first();
        $this->actingAs($admin, 'admin');

        $attendance = Attendance::with(['user', 'workBreaks', 'attendanceChange.workBreakChanges'])
            ->where('work_date', '2025-06-25')
            ->firstOrFail();

        $response = $this->get('/attendance/' . $attendance->id);
        $response->assertStatus(200);

        $response->assertSee($attendance->user->name);

        $workDate = Carbon::parse($attendance->work_date);
        $year = $workDate->format('Y年');
        $date = $workDate->format('n月j日');
        $response->assertSee($year);
        $response->assertSee($date);

        if ($attendance->attendanceChange && $attendance->attendanceChange->status === 'pending') {
            $change = $attendance->attendanceChange;
            if ($change->new_clock_in) {
                $clockIn = Carbon::parse($change->new_clock_in)->format('H:i');
                $response->assertSee($clockIn);
            }
            if ($change->new_clock_out) {
                $clockOut = Carbon::parse($change->new_clock_out)->format('H:i');
                $response->assertSee($clockOut);
            }
        } else {
            if ($attendance->clock_in) {
                $clockIn = Carbon::parse($attendance->clock_in)->format('H:i');
                $response->assertSee($clockIn);
            }
            if ($attendance->clock_out) {
                $clockOut = Carbon::parse($attendance->clock_out)->format('H:i');
                $response->assertSee($clockOut);
            }
        }

        foreach ($attendance->workBreaks as $break) {
            if ($break->break_start) {
                $response->assertSee(Carbon::parse($break->break_start)->format('H:i'));
            }
            if ($break->break_end) {
                $response->assertSee(Carbon::parse($break->break_end)->format('H:i'));
            }
        }
    }

    public function test_admin_attendance_clock_change_error_message()
    {
        $admin = \App\Models\Admin::where('email', 'admin@gmail.com')->first();
        $this->actingAs($admin, 'admin');

        $attendance = Attendance::with(['user', 'workBreaks', 'attendanceChange.workBreakChanges'])
            ->where('work_date', '2025-06-25')
            ->firstOrFail();

        $detailResponse = $this->get("/attendance/{$attendance->id}");
        $detailResponse->assertStatus(200);

        $invalidClockIn = '18:00';
        $clockOut = '10:00';

        $postData = [
            'attendance_id' => $attendance->id,
            'new_clock_in' => $invalidClockIn,
            'new_clock_out' => $clockOut,
            'remarks' => 'テストの変更申請'
        ];

        $response = $this->post("/attendance/{$attendance->id}", $postData);

        $response->assertSessionHasErrors([
            'new_clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_admin_break_start_change_error_message()
    {
        $admin = \App\Models\Admin::where('email', 'admin@gmail.com')->first();
        $this->actingAs($admin, 'admin');

        $attendance = Attendance::with(['user', 'workBreaks', 'attendanceChange.workBreakChanges'])
            ->where('work_date', '2025-06-25')
            ->firstOrFail();

        $detailResponse = $this->get("/attendance/{$attendance->id}");
        $detailResponse->assertStatus(200);

        $clockIn = '9:00';
        $clockOut = '17:00';
        $invalidBreakStart = '18:00';
        $breakEnd = '19:00';

        $postData = [
            'attendance_id' => $attendance->id,
            'new_clock_in' => $clockIn,
            'new_clock_out' => $clockOut,
            'remarks' => 'テストの変更申請',
            'breaks' => [
                [
                    'start' => $invalidBreakStart,
                    'end' => $breakEnd
                ]
            ],
            'work_break_id' => [$attendance->WorkBreaks->first()->id ?? null],
        ];

        $response = $this->post("/attendance/{$attendance->id}", $postData);

        $response->assertSessionHasErrors([
            'breaks.0' => '休憩時間が勤務時間外です',
        ]);
    }

    public function test_admin_break_end_change_error_message()
    {
        $admin = \App\Models\Admin::where('email', 'admin@gmail.com')->first();
        $this->actingAs($admin, 'admin');

        $attendance = Attendance::with(['user', 'workBreaks', 'attendanceChange.workBreakChanges'])
            ->where('work_date', '2025-06-25')
            ->firstOrFail();

        $detailResponse = $this->get("/attendance/{$attendance->id}");
        $detailResponse->assertStatus(200);

        $clockIn = '9:00';
        $clockOut = '17:00';
        $breakStart = '16:30';
        $invalidBreakEnd = '17:30';

        $postData = [
            'attendance_id' => $attendance->id,
            'new_clock_in' => $clockIn,
            'new_clock_out' => $clockOut,
            'remarks' => 'テストの変更申請',
            'breaks' => [
                [
                    'start' => $breakStart,
                    'end' => $invalidBreakEnd
                ]
            ],
            'work_break_id' => [$attendance->WorkBreaks->first()->id ?? null],
        ];

        $response = $this->post("/attendance/{$attendance->id}", $postData);

        $response->assertSessionHasErrors([
            'breaks.0' => '休憩時間が勤務時間外です',
        ]);
    }

    public function test_remarks_error_message()
    {
        $admin = \App\Models\Admin::where('email', 'admin@gmail.com')->first();
        $this->actingAs($admin, 'admin');

        $attendance = Attendance::with(['user', 'workBreaks', 'attendanceChange.workBreakChanges'])
            ->where('work_date', '2025-06-25')
            ->firstOrFail();

        $detailResponse = $this->get("/attendance/{$attendance->id}");
        $detailResponse->assertStatus(200);

        $clockIn = '9:00';
        $clockOut = '17:00';

        $postData = [
            'attendance_id' => $attendance->id,
            'new_clock_in' => $clockIn,
            'new_clock_out' => $clockOut,
            'remarks' => '',
        ];

        $response = $this->post("/attendance/{$attendance->id}", $postData);

        $response->assertSessionHasErrors([
            'remarks' => '備考を記入してください',
        ]);
    }
}
