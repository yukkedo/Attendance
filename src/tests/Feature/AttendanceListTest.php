<?php

namespace Tests\Feature;

use App\Models\Attendance;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AttendanceListTest extends TestCase
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

    public function test_attendance_list_display_user_data()
    {
        $user = \App\Models\User::where('email', 'user1@example.com')->first();
        $user->markEmailAsVerified();
        $this->actingAs($user);

        $response = $this->get('/attendance/list');

        $month = now()->format('Y-m');
        $firstDay = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $finalDay = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

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

    public function test_attendance_list_current_month()
    {
        $user = \App\Models\User::where('email', 'user1@example.com')->first();
        $user->markEmailAsVerified();
        $this->actingAs($user);

        $response = $this->get('/attendance/list');

        $currentMonth = Carbon::now()->format('Y/m');

        $response->assertSee($currentMonth);
    }

    public function test_attendance_list_previous_month()
    {
        $user = \App\Models\User::where('email', 'user1@example.com')->first();
        $user->markEmailAsVerified();
        $this->actingAs($user);

        $nextMonth = now()->subMonth()->format('Y-m');

        $response = $this->get('/attendance/list/' . $nextMonth);

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
        $user = \App\Models\User::where('email', 'user1@example.com')->first();
        $user->markEmailAsVerified();
        $this->actingAs($user);

        $attendance = Attendance::where('user_id', $user->id)->first();

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        $response->assertSee("/attendance/{$attendance->id}");

        $detailResponse = $this->get("/attendance/{$attendance->id}");
        $detailResponse->assertStatus(200);
    }
}
