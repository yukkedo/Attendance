<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use App\Models\WorkBreak;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminAttendanceListTest extends TestCase
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

    public function test_admin_attendance_list_all_user()
    {
        $admin = \App\Models\Admin::where('email', 'admin@gmail.com')->first();
        $this->actingAs($admin, 'admin');

        $testDate = '2025-06-26';
        $response = $this->get('/admin/attendance/list/' . $testDate);
        $response->assertStatus(200);



        $attendances = Attendance::with(['user', 'workBreaks'])
            ->where('work_date', $testDate)
            ->get();

        foreach ($attendances as $attendance) {
            $userName = $attendance->user->name;
            $response->assertSee($userName);

            $clockIn = $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in) : null;
            $clockOut = $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out) : null;

            if($clockIn && $clockOut) {
                $response->assertSee($clockIn->format('H:i'));
                $response->assertSee($clockOut->format('H:i'));
            }

            $totalBreakMinutes = 0;
            foreach ($attendance->workBreaks as $break) {
                if ($break->break_start && $break->break_end) {
                    $start = \Carbon\Carbon::parse($break->break_start);
                    $end = \Carbon\Carbon::parse($break->break_end);
                    $totalBreakMinutes += $end->diffInMinutes($start);
                }
            }

            $breakTimeFormatted = $totalBreakMinutes > 0
                ? sprintf('%d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60)
                : '';
            $response->assertSee($breakTimeFormatted);

            if ($clockIn && $clockOut) {
                $workMinutes = $clockOut->diffInMinutes($clockIn) - $totalBreakMinutes;
                $workTimeFormatted = $workMinutes > 0
                    ? sprintf('%d:%02d', floor($workMinutes / 60), $workMinutes % 60)
                    : '';
                $response->assertSee($workTimeFormatted);
            }

            $response->assertSee('/attendance/' . $attendance->id);
        }
    }

    public function test_admin_attendance_list_show_today()
    {
        $admin = \App\Models\Admin::where('email', 'admin@gmail.com')->first();
        $this->actingAs($admin, 'admin');

        $today = Carbon::today()->format('Y/m/d');
        $response = $this->get('/admin/attendance/list');
        $response->assertSee($today);
    }

    public function test_admin_attendance_list_prev_day()
    {
        $admin = \App\Models\Admin::where('email', 'admin@gmail.com')->first();
        $this->actingAs($admin, 'admin');

        $testDate = '2025-06-26';
        $prevDate = Carbon::parse($testDate)->subDay()->toDateString();

        $response = $this->get('admin/attendance/list/' . $prevDate);
        $response->assertStatus(200);

        $attendances = Attendance::with(['user', 'workBreaks'])
            ->where('work_date', $prevDate)
            ->get();

        foreach ($attendances as $attendance) {
            $userName = $attendance->user->name;
            $response->assertSee($userName);

            $clockIn = $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in) : null;
            $clockOut = $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out) : null;

            if ($clockIn && $clockOut) {
                $response->assertSee($clockIn->format('H:i'));
                $response->assertSee($clockOut->format('H:i'));
            }

            $totalBreakMinutes = 0;
            foreach ($attendance->workBreaks as $break) {
                if ($break->break_start && $break->break_end) {
                    $start = \Carbon\Carbon::parse($break->break_start);
                    $end = \Carbon\Carbon::parse($break->break_end);
                    $totalBreakMinutes += $end->diffInMinutes($start);
                }
            }

            $breakTimeFormatted = $totalBreakMinutes > 0
                ? sprintf('%d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60)
                : '';
            $response->assertSee($breakTimeFormatted);

            if ($clockIn && $clockOut) {
                $workMinutes = $clockOut->diffInMinutes($clockIn) - $totalBreakMinutes;
                $workTimeFormatted = $workMinutes > 0
                    ? sprintf('%d:%02d', floor($workMinutes / 60), $workMinutes % 60)
                    : '';
                $response->assertSee($workTimeFormatted);
            }

            $response->assertSee('/attendance/' . $attendance->id);
        }
    }

    public function test_admin_attendance_list_next_day()
    {
        $admin = \App\Models\Admin::where('email', 'admin@gmail.com')->first();
        $this->actingAs($admin, 'admin');

        $testDate = '2025-06-26';
        $nextDate = Carbon::parse($testDate)->addDay()->toDateString();

        $response = $this->get('admin/attendance/list/' . $nextDate);
        $response->assertStatus(200);

        $attendances = Attendance::with(['user', 'workBreaks'])
            ->where('work_date', $nextDate)
            ->get();

        foreach ($attendances as $attendance) {
            $userName = $attendance->user->name;
            $response->assertSee($userName);

            $clockIn = $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in) : null;
            $clockOut = $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out) : null;

            if ($clockIn && $clockOut) {
                $response->assertSee($clockIn->format('H:i'));
                $response->assertSee($clockOut->format('H:i'));
            }

            $totalBreakMinutes = 0;
            foreach ($attendance->workBreaks as $break) {
                if ($break->break_start && $break->break_end) {
                    $start = \Carbon\Carbon::parse($break->break_start);
                    $end = \Carbon\Carbon::parse($break->break_end);
                    $totalBreakMinutes += $end->diffInMinutes($start);
                }
            }

            $breakTimeFormatted = $totalBreakMinutes > 0
                ? sprintf('%d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60)
                : '';
            $response->assertSee($breakTimeFormatted);

            if ($clockIn && $clockOut) {
                $workMinutes = $clockOut->diffInMinutes($clockIn) - $totalBreakMinutes;
                $workTimeFormatted = $workMinutes > 0
                    ? sprintf('%d:%02d', floor($workMinutes / 60), $workMinutes % 60)
                    : '';
                $response->assertSee($workTimeFormatted);
            }

            $response->assertSee('/attendance/' . $attendance->id);
        }
    }
}
