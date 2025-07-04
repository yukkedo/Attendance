<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminGetUserTest extends TestCase
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

    public function test_admin_get_user_list()
    {
        $admin = \App\Models\Admin::where('email', 'admin@gmail.com')->first();
        $this->actingAs($admin, 'admin');

        $response = $this->get('/admin/staff/list');
        $response->assertStatus(200);

        $users = User::all(['name', 'email']);
        foreach ( $users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }

    public function test_admin_staff_attendance_list()
    {
        $admin = \App\Models\Admin::where('email', 'admin@gmail.com')->first();
        $this->actingAs($admin, 'admin');

        $user = User::where('email', 'user1@example.com')->firstOrFail();

        $attendances = Attendance::with('workBreaks')
            ->where('user_id', $user->id)
            ->where('work_date')
            ->get();

        $response = $this->get('/admin/attendance/staff/' . $user->id);
        $response->assertStatus(200);

        $response->assertSee($user->name);

        foreach ($attendances as $attendance) {
            $date = \Carbon\Carbon::parse($attendance->work_date)
                ->format('m/d（' . ['日', '月', '火', '水', '木', '金', '土'][\Carbon\Carbon::parse($attendance->work_date)->dayOfWeek] . '）');
            $response->assertSee($date);
            
            if ($attendance->clock_in) {
                $response->assertSee(\Carbon\Carbon::parse($attendance->clock_in)->format('H:i'));
            }
            if ($attendance->clock_out) {
                $response->assertSee(\Carbon\Carbon::parse($attendance->clock_out)->format('H:i'));
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
            if ($breakTimeFormatted) {
                $response->assertSee($breakTimeFormatted);
            }

            if ($attendance->clock_in && $attendance->clock_out) {
                $workMinutes = \Carbon\Carbon::parse($attendance->clock_out)
                    ->diffInMinutes(\Carbon\Carbon::parse($attendance->clock_in)) - $totalBreakMinutes;
                $workTimeFormatted = $workMinutes > 0
                    ? sprintf('%d:%02d', floor($workMinutes / 60), $workMinutes % 60)
                    : '';
                if ($workTimeFormatted) {
                    $response->assertSee($workTimeFormatted);
                }
            }
        }
    }

    public function test_admin_staff_attendance_list_previous_month()
    {
        $admin = \App\Models\Admin::where('email', 'admin@gmail.com')->first();
        $this->actingAs($admin, 'admin');

        $user = User::where('email', 'user1@example.com')->firstOrFail();

        Carbon::setTestNow(Carbon::create(2025, 5));

        $previousMonth = now()->subMonth()->format('Y-m');

        $response = $this->get("/admin/attendance/staff/{$user->id}/{$previousMonth}");
        $response->assertStatus(200);

        $firstDay = Carbon::createFromFormat('Y-m', $previousMonth)->startOfMonth();
        $finalDay = Carbon::createFromFormat('Y-m', $previousMonth)->endOfMonth();

        $attendances = Attendance::with('workBreaks')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$firstDay, $finalDay])
            ->get();

        foreach ($attendances as $attendance) {
            if ($attendance->clock_in) {
                $response->assertSee(Carbon::parse($attendance->clock_in)->format('H:i'));
            }
            if ($attendance->clock_out) {
                $response->assertSee(Carbon::parse($attendance->clock_out)->format('H:i'));
            }

            $totalBreak = $attendance->workBreaks->reduce(function ($carry, $break) {
                if ($break->break_start && $break->break_end) {
                    return $carry + Carbon::parse($break->break_end)->diffInMinutes(Carbon::parse($break->break_start));
                }
                return $carry;
            }, 0);

            $breakFormatted = $totalBreak > 0 ? sprintf('%d:%02d', intdiv($totalBreak, 60), $totalBreak % 60) : '';
            if ($breakFormatted) {
                $response->assertSee($breakFormatted);
            }

            if ($attendance->clock_out) {
                $start = Carbon::parse($attendance->clock_in);
                $end = Carbon::parse($attendance->clock_out);
                $workMinutes = $end->diffInMinutes($start) - $totalBreak;

                $workFormatted = sprintf('%d:%02d', intdiv($workMinutes, 60), $workMinutes % 60);
                $response->assertSee($workFormatted);
            }
        }
    }

    public function test_admin_staff_attendance_list_next_month()
    {
        $admin = \App\Models\Admin::where('email', 'admin@gmail.com')->first();
        $this->actingAs($admin, 'admin');

        $user = User::where('email', 'user1@example.com')->firstOrFail();

        Carbon::setTestNow(Carbon::create(2025, 5));

        $nextMonth = now()->addMonth()->format('Y-m');

        $response = $this->get("/admin/attendance/staff/{$user->id}/{$nextMonth}");
        $response->assertStatus(200);

        $firstDay = Carbon::createFromFormat('Y-m', $nextMonth)->startOfMonth();
        $finalDay = Carbon::createFromFormat('Y-m', $nextMonth)->endOfMonth();

        $attendances = Attendance::with('workBreaks')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$firstDay, $finalDay])
            ->get();

        foreach ($attendances as $attendance) {
            if ($attendance->clock_in) {
                $response->assertSee(Carbon::parse($attendance->clock_in)->format('H:i'));
            }
            if ($attendance->clock_out) {
                $response->assertSee(Carbon::parse($attendance->clock_out)->format('H:i'));
            }

            $totalBreak = $attendance->workBreaks->reduce(function ($carry, $break) {
                if ($break->break_start && $break->break_end) {
                    return $carry + Carbon::parse($break->break_end)->diffInMinutes(Carbon::parse($break->break_start));
                }
                return $carry;
            }, 0);

            $breakFormatted = $totalBreak > 0 ? sprintf('%d:%02d', intdiv($totalBreak, 60), $totalBreak % 60) : '';
            if ($breakFormatted) {
                $response->assertSee($breakFormatted);
            }

            if ($attendance->clock_out) {
                $start = Carbon::parse($attendance->clock_in);
                $end = Carbon::parse($attendance->clock_out);
                $workMinutes = $end->diffInMinutes($start) - $totalBreak;

                $workFormatted = sprintf('%d:%02d', intdiv($workMinutes, 60), $workMinutes % 60);
                $response->assertSee($workFormatted);
            }
        }
    }

    public function test_attendance_list_detail()
    {
        $admin = \App\Models\Admin::where('email', 'admin@gmail.com')->first();
        $this->actingAs($admin, 'admin');

        $user = User::where('email', 'user1@example.com')->firstOrFail();

        $attendance = Attendance::with('workBreaks')
            ->where('user_id', $user->id)
            ->latest('work_date')
            ->firstOrFail();

        $response = $this->get('/admin/attendance/staff/' . $user->id);
        $response->assertStatus(200);

        $response->assertSee("/attendance/{$attendance->id}");

        $detailResponse = $this->get("/attendance/{$attendance->id}");
        $detailResponse->assertStatus(200);
    }
}
