<?php

namespace Tests\Feature;

use App\Models\Attendance;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AttendanceDetailTest extends TestCase
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

    public function test_attendance_detail_user_name()
    {
        $user = \App\Models\User::where('email', 'user1@example.com')->first();
        $user->markEmailAsVerified();
        $this->actingAs($user);

        $attendance = Attendance::where('user_id', $user->id)->first();

        $detailResponse = $this->get("/attendance/{$attendance->id}");
        $detailResponse->assertSee($user->name);
    }

    public function test_attendance_detail_date()
    {
        $user = \App\Models\User::where('email', 'user1@example.com')->first();
        $user->markEmailAsVerified();
        $this->actingAs($user);

        $attendance = Attendance::where('user_id', $user->id)->first();

        $detailResponse = $this->get("/attendance/{$attendance->id}");
        $detailResponse->assertStatus(200);

        $workDate = Carbon::parse($attendance->work_date);
        $year = $workDate->format('Y年');
        $date = $workDate->format('n月j日');

        $detailResponse->assertSee($year);
        $detailResponse->assertSee($date);
    }

    

    public function test_attendance_detail_break_time()
    {
        $user = \App\Models\User::where('email', 'user1@example.com')->first();
        $user->markEmailAsVerified();
        $this->actingAs($user);

        $attendance = Attendance::with('workBreaks')
            ->where('user_id', $user->id)
            ->first();

        $detailResponse = $this->get("/attendance/{$attendance->id}");
        $detailResponse->assertStatus(200);

        foreach ($attendance->workBreaks as $break) {
            if ($break->break_start) {
                $detailResponse->assertSee(Carbon::parse($break->break_start)->format('H:i'));
            }
            if ($break->break_end) {
                $detailResponse->assertSee(Carbon::parse($break->break_end)->format('H:i'));
            }
        }
    }
}
