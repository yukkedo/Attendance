<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\WorkBreak;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StampTest extends TestCase
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

    public function test_clock_in()
    {
        $user = \App\Models\User::where('email', 'user1@example.com')->first();
        $user->markEmailAsVerified();
        $this->actingAs($user);

        Attendance::where('user_id', $user->id)
            ->where('work_date', today()->toDateString())
            ->delete();

        $this->get('/attendance')
            ->assertSee('出勤');

        $this->post('/attendance/clockIn')
            ->assertRedirect('/attendance');

        $this->get('attendance')
            ->assertSee('出勤中');
    }

    public function test_clock_in_only_once()
    {
        $user = \App\Models\User::where('email', 'user1@example.com')->first();
        $user->markEmailAsVerified();
        $this->actingAs($user);

        $this->get('/attendance')
            ->assertDontSee('出勤');
    }

    public function test_clock_in_admin_confirm()
    {
        $user = \App\Models\User::where('email', 'user1@example.com')->first();
        $user->markEmailAsVerified();
        $this->actingAs($user);

        Attendance::where('user_id', $user->id)
            ->where('work_date', today()->toDateString())
            ->delete();

        $this->post('/attendance/clockIn')
            ->assertRedirect('/attendance');

        $attendance = Attendance::where('user_id', $user->id)->first();
        $clockIn = \Carbon\Carbon::parse($attendance->clock_in)->format('H:i');

        $admin = \App\Models\Admin::where('email', 'admin@gmail.com')->first();
        $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/list')
            ->assertSee($user->name)
            ->assertSee($clockIn);
    }

    public function test_break_start()
    {
        $user = \App\Models\User::where('email', 'user1@example.com')->first();
        $user->markEmailAsVerified();
        $this->actingAs($user);

        Attendance::where('user_id', $user->id)
            ->where('work_date', today()->toDateString())
            ->delete();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now()->subHour(2),
            'clock_out' => null,
        ]);

        $this->get('/attendance')
            ->assertSee('休憩入');

        $this->post('/attendance/breakStart')
            ->assertRedirect('/attendance');

        $this->get('/attendance')
            ->assertSee('休憩中');
    }

    public function test_break_start_many_times()
    {
        $user = \App\Models\User::where('email', 'user1@example.com')->first();
        $user->markEmailAsVerified();
        $this->actingAs($user);

        Attendance::where('user_id', $user->id)
            ->where('work_date', today()->toDateString())
            ->delete();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now()->subHour(2),
            'clock_out' => null,
        ]);

        $this->post('/attendance/breakStart')
            ->assertRedirect('/attendance');

        $this->post('/attendance/breakEnd')
            ->assertRedirect('/attendance');

        $this->get('/attendance')
            ->assertSee('休憩入');
    }

    public function test_break_end()
    {
        $user = \App\Models\User::where('email', 'user1@example.com')->first();
        $user->markEmailAsVerified();
        $this->actingAs($user);

        Attendance::where('user_id', $user->id)
            ->where('work_date', today()->toDateString())
            ->delete();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now()->subHour(2),
            'clock_out' => null,
        ]);

        $this->post('/attendance/breakStart')
            ->assertRedirect('/attendance');

        $this->get('/attendance')
            ->assertSee('休憩戻');

        $this->post('/attendance/breakEnd')
            ->assertRedirect('/attendance');

        $this->get('/attendance')
            ->assertSee('出勤中');
    }

    public function test_break_end_many_times()
    {
        $user = \App\Models\User::where('email', 'user1@example.com')->first();
        $user->markEmailAsVerified();
        $this->actingAs($user);

        Attendance::where('user_id', $user->id)
            ->where('work_date', today()->toDateString())
            ->delete();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now()->subHour(2),
            'clock_out' => null,
        ]);

        $this->post('/attendance/breakStart')
            ->assertRedirect('/attendance');

        $this->post('/attendance/breakEnd')
            ->assertRedirect('/attendance');

        $this->post('/attendance/breakStart')
            ->assertRedirect('/attendance');

        $this->get('/attendance')
            ->assertSee('休憩戻');
    }

    public function test_break_check_time()
    {
        $user = \App\Models\User::where('email', 'user1@example.com')->first();
        $user->markEmailAsVerified();
        $this->actingAs($user);

        Attendance::where('user_id', $user->id)
            ->where('work_date', today()->toDateString())
            ->delete();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now()->subHour(2),
            'clock_out' => null,
        ]);

        $this->post('/attendance/breakStart')
            ->assertRedirect('/attendance');

        $this->post('/attendance/breakEnd')
            ->assertRedirect('/attendance');

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', today()->toDateString())
            ->first();

        $workBreak = WorkBreak::where('attendance_id', $attendance->id)->first();

        $workBreak->update([
            'break_end' => \Carbon\Carbon::parse($workBreak->break_start)->addMinutes(15),
        ]);

        $this->get('/attendance/list')
            ->assertSee('0:15');
    }

    public function test_clock_out()
    {
        $user = \App\Models\User::where('email', 'user1@example.com')->first();
        $user->markEmailAsVerified();
        $this->actingAs($user);

        Attendance::where('user_id', $user->id)
            ->where('work_date', today()->toDateString())
            ->delete();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now()->subHour(2),
            'clock_out' => null,
        ]);

        $this->get('/attendance')
            ->assertSee('退勤');

        $this->post('/attendance/clockOut')
            ->assertRedirect('/attendance');

        $this->get('/attendance')
            ->assertSee('退勤済');
    }

    public function test_clock_out_admin_confirm()
    {
        $user = \App\Models\User::where('email', 'user1@example.com')->first();
        $user->markEmailAsVerified();
        $this->actingAs($user);

        Attendance::where('user_id', $user->id)
            ->where('work_date', today()->toDateString())
            ->delete();

        $this->post('/attendance/clockIn')->assertRedirect('/attendance');
        $this->post('/attendance/clockOut')->assertRedirect('/attendance');

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', today()->toDateString())
            ->first();
        $clockOut = \Carbon\Carbon::parse($attendance->clock_out)->format('H:i');

        $admin = \App\Models\Admin::where('email', 'admin@gmail.com')->first();
        $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/list')
            ->assertSee($clockOut);
    }
}
