<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Attendance_change;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AttendanceChangeTest extends TestCase
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

    // public function test_attendance_clock_change_error_message()
    // {
    //     $user = \App\Models\User::where('email', 'user1@example.com')->first();
    //     $user->markEmailAsVerified();
    //     $this->actingAs($user);

    //     $attendance = Attendance::where('user_id', $user->id)->first();

    //     $detailResponse = $this->get("/attendance/{$attendance->id}");
    //     $detailResponse->assertStatus(200);

    //     $invalidClockIn = '18:00';
    //     $clockOut = '10:00';

    //     $postData = [
    //         'attendance_id' => $attendance->id,
    //         'new_clock_in' => $invalidClockIn,
    //         'new_clock_out' => $clockOut,
    //         'remarks' => 'テストの変更申請'
    //     ];

    //     $response = $this->post("/attendance/{$attendance->id}", $postData);

    //     $response->assertSessionHasErrors([
    //         'new_clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
    //     ]);
    // }

    // public function test_break_start_change_error_message()
    // {
    //     $user = \App\Models\User::where('email', 'user1@example.com')->first();
    //     $user->markEmailAsVerified();
    //     $this->actingAs($user);

    //     $attendance = Attendance::where('user_id', $user->id)->first();

    //     $detailResponse = $this->get("/attendance/{$attendance->id}");
    //     $detailResponse->assertStatus(200);

    //     $clockIn = '9:00';
    //     $clockOut = '17:00';
    //     $invalidBreakStart = '18:00';
    //     $breakEnd = '19:00';

    //     $postData = [
    //         'attendance_id' => $attendance->id,
    //         'new_clock_in' => $clockIn,
    //         'new_clock_out' => $clockOut,
    //         'remarks' => 'テストの変更申請',
    //         'breaks' => [
    //             ['start' => $invalidBreakStart,
    //              'end'=> $breakEnd]
    //         ],
    //         'work_break_id' => [$attendance->WorkBreaks->first()->id ?? null],  
    //     ];

    //     $response = $this->post("/attendance/{$attendance->id}", $postData);

    //     $response->assertSessionHasErrors([
    //         'breaks.0' => '休憩時間が勤務時間外です',
    //     ]);
    // }

    // public function test_break_end_change_error_message()
    // {
    //     $user = \App\Models\User::where('email', 'user1@example.com')->first();
    //     $user->markEmailAsVerified();
    //     $this->actingAs($user);

    //     $attendance = Attendance::where('user_id', $user->id)->first();

    //     $detailResponse = $this->get("/attendance/{$attendance->id}");
    //     $detailResponse->assertStatus(200);

    //     $clockIn = '9:00';
    //     $clockOut = '17:00';
    //     $breakStart = '16:30';
    //     $invalidBreakEnd = '17:30';

    //     $postData = [
    //         'attendance_id' => $attendance->id,
    //         'new_clock_in' => $clockIn,
    //         'new_clock_out' => $clockOut,
    //         'remarks' => 'テストの変更申請',
    //         'breaks' => [
    //             [
    //                 'start' => $breakStart,
    //                 'end' => $invalidBreakEnd
    //             ]
    //         ],
    //         'work_break_id' => [$attendance->WorkBreaks->first()->id ?? null],
    //     ];

    //     $response = $this->post("/attendance/{$attendance->id}", $postData);

    //     $response->assertSessionHasErrors([
    //         'breaks.0' => '休憩時間が勤務時間外です',
    //     ]);
    // }

    // public function test_remarks_error_message()
    // {
    //     $user = \App\Models\User::where('email', 'user1@example.com')->first();
    //     $user->markEmailAsVerified();
    //     $this->actingAs($user);

    //     $attendance = Attendance::where('user_id', $user->id)->first();

    //     $detailResponse = $this->get("/attendance/{$attendance->id}");
    //     $detailResponse->assertStatus(200);

    //     $clockIn = '9:00';
    //     $clockOut = '17:00';

    //     $postData = [
    //         'attendance_id' => $attendance->id,
    //         'new_clock_in' => $clockIn,
    //         'new_clock_out' => $clockOut,
    //         'remarks' => '',
    //     ];

    //     $response = $this->post("/attendance/{$attendance->id}", $postData);

    //     $response->assertSessionHasErrors([
    //         'remarks' => '備考を記入してください',
    //     ]);
    // }

    // public function test_attendance_change_execution_admin_confirm()
    // {
    //     $user = \App\Models\User::where('email', 'user1@example.com')->first();
    //     $user->markEmailAsVerified();
    //     $this->actingAs($user);

    //     $attendance = Attendance::where('user_id', $user->id)->first();

    //     $postData = [
    //         'attendance_id' => $attendance->id,
    //         'new_clock_in' => '09:00',
    //         'new_clock_out' => '17:00',
    //         'remarks' => 'テストの変更申請',
    //         'breaks' => [
    //             [
    //                 'start' => '12:00',
    //                 'end' => '13:00'
    //             ]
    //         ],
    //         'work_break_id' => [$attendance->WorkBreaks->first()->id ?? null],
    //     ];

    //     $response = $this->post("/attendance/{$attendance->id}", $postData);

    //     $this->assertDatabaseHas('attendance_changes', [
    //         'attendance_id' => $attendance->id,
    //         'user_id' => $user->id,
    //         'remarks' => 'テストの変更申請',
    //         'status' => 'pending'
    //     ]);

    //     $admin = \App\Models\Admin::where('email', 'admin@gmail.com')->first();
    //     $this->actingAs($admin, 'admin');

    //     $listResponse = $this->get('/stamp_correction_request/list');
    //     $listResponse->assertStatus(200);

    //     $listResponse->assertSeeText('承認待ち');
    //     $listResponse->assertSeeText($user->name);
    //     $listResponse->assertSeeText('テストの変更申請');

    //     $attendanceChange = Attendance_change::where('user_id', $user->id)->latest()->first();
    //     $detailResponse = $this->get("/stamp_correction_request/approve/{$attendanceChange->id}");
    //     $detailResponse->assertSeeText('テストの変更申請');
    //     $detailResponse->assertSeeText('09:00');
    //     $detailResponse->assertSeeText('17:00');
    // }

    // public function test_attendance_change_application_list_pending_confirm()
    // {
    //     $user = \App\Models\User::where('email', 'user1@example.com')->first();
    //     $user->markEmailAsVerified();
    //     $this->actingAs($user);

    //     $attendance = Attendance::where('user_id', $user->id)->first();

    //     $postData = [
    //         'attendance_id' => $attendance->id,
    //         'new_clock_in' => '09:00',
    //         'new_clock_out' => '17:00',
    //         'remarks' => 'テストの変更申請',
    //         'breaks' => [
    //             [
    //                 'start' => '12:00',
    //                 'end' => '13:00'
    //             ]
    //         ],
    //         'work_break_id' => [$attendance->WorkBreaks->first()->id ?? null],
    //     ];

    //     $response = $this->post("/attendance/{$attendance->id}", $postData);

    //     $this->assertDatabaseHas('attendance_changes', [
    //         'attendance_id' => $attendance->id,
    //         'user_id' => $user->id,
    //         'remarks' => 'テストの変更申請',
    //         'status' => 'pending'
    //     ]);

    //     $listResponse = $this->get('/stamp_correction_request/list');
    //     $listResponse->assertSeeText('承認待ち');
    //     $listResponse->assertSeeText($user->name);
    //     $listResponse->assertSeeText('テストの変更申請');
    // }

    // public function test_attendance_change_application_list_approved_confirm()
    // {
    //     $user = \App\Models\User::where('email', 'user1@example.com')->first();
    //     $user->markEmailAsVerified();
    //     $this->actingAs($user);

    //     $attendance = Attendance::where('user_id', $user->id)->first();

    //     $postData = [
    //         'attendance_id' => $attendance->id,
    //         'new_clock_in' => '09:00',
    //         'new_clock_out' => '17:00',
    //         'remarks' => 'テストの変更申請',
    //         'breaks' => [
    //             [
    //                 'start' => '12:00',
    //                 'end' => '13:00'
    //             ]
    //         ],
    //         'work_break_id' => [$attendance->WorkBreaks->first()->id ?? null],
    //     ];

    //     $response = $this->post("/attendance/{$attendance->id}", $postData);

    //     $attendanceChange = Attendance_change::where('user_id', $user->id)->latest()->first();
    //     $this->assertEquals('pending', $attendanceChange->status);

    //     $admin = \App\Models\Admin::where('email', 'admin@gmail.com')->first();
    //     $this->actingAs($admin, 'admin');

    //     $approvedResponse = $this->get("/stamp_correction_request/approve/{$attendanceChange->id}");
    //     $approvedResponse->assertSeeText('承認');

    //     $this->post("/stamp_correction_request/approve/{$attendanceChange->id}");
    //     $attendanceChange->refresh();
    //     $this->assertEquals('approved', $attendanceChange->status);

    //     $this->actingAs($user);

    //     $approvedListResponse = $this->get('/stamp_correction_request/list?tab=approved');
    //     $approvedListResponse->assertSeeText('承認済み');
    //     $approvedListResponse->assertSeeText($user->name);
    //     $approvedListResponse->assertSeeText('テストの変更申請');
    // }

    public function test_application_list_detail_page()
    {
        $user = \App\Models\User::where('email', 'user1@example.com')->first();
        $user->markEmailAsVerified();
        $this->actingAs($user);

        $attendance = Attendance::where('user_id', $user->id)->first();

        $postData = [
            'attendance_id' => $attendance->id,
            'new_clock_in' => '09:00',
            'new_clock_out' => '17:00',
            'remarks' => '詳細画面テスト',
            'status' => 'pending',
            'breaks' => [
                [
                    'start' => '12:00',
                    'end' => '13:00'
                ]
            ],
            'work_break_id' => [$attendance->WorkBreaks->first()->id ?? null],
        ];
        $this->post("/attendance/{$attendance->id}", $postData);

        $listResponse = $this->get('/stamp_correction_request/list');
        $listResponse->assertSeeText('詳細');

        $attendanceChange = Attendance_change::where('user_id', $user->id)->latest()->first();

        $detailResponse = $this->get("/attendance/{$attendanceChange->attendance_id}");
        $detailResponse->assertSeeText('詳細画面テスト');
        $detailResponse->assertSee('09:00');
        $detailResponse->assertSee('17:00');
    }
}
