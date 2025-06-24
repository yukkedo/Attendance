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

class AttendanceTest extends TestCase
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

    public function test_get_datetime()
    {
        $user = \App\Models\User::where('email', 'user1@example.com')->first();
        $user->markEmailAsVerified();
        $this->actingAs($user);

        $now = now()->locale('ja')->timezone('Asia/Tokyo');

        $weekdayMap = ['日', '月', '火', '水', '木', '金', '土'];
        $weekday = $weekdayMap[$now->dayOfWeek];

        $expectedDate = $now->format("Y年n月j日") . "($weekday)" ;
        $expectedTime = $now->format("H:i");

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee($expectedDate);
        $response->assertSee($expectedTime);
    }

    public function test_status_off_duty()
    {
        $user = \App\Models\User::where('email', 'user1@example.com')->first();
        $user->markEmailAsVerified();
        $this->actingAs($user);

        Attendance::where('user_id', $user->id)
            ->where('work_date', today()->toDateString())
            ->delete();

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    public function test_status_at_work()
    {
        $user = \App\Models\User::where('email', 'user1@example.com')->first();
        $user->markEmailAsVerified();
        $this->actingAs($user);

        Attendance::where('user_id', $user->id)
            ->where('work_date', today()->toDateString())
            ->delete();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::now()->toDateString(),
            'clock_in' => Carbon::now()->subHours(2),
            'clock_out' => null,
        ]);
        
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    public function test_status_during_break()
    {
        $user = \App\Models\User::where('email', 'user1@example.com')->first();
        $user->markEmailAsVerified();
        $this->actingAs($user);

        Attendance::where('user_id', $user->id)
            ->where('work_date', today()->toDateString())
            ->delete();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::now()->toDateString(),
            'clock_in' => Carbon::now()->subHours(2),
            'clock_out' => null,
        ]);

        WorkBreak::create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::now()->subHour(),
            'break_end' => null,
        ]);

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    public function test_status_clocked_out()
    {
        $user = \App\Models\User::where('email', 'user1@example.com')->first();
        $user->markEmailAsVerified();
        $this->actingAs($user);

        Attendance::where('user_id', $user->id)
            ->where('work_date', today()->toDateString())
            ->delete();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::now()->toDateString(),
            'clock_in' => Carbon::now()->subHours(2),
            'clock_out' => Carbon::now()->subHour(),
        ]);

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }
}
