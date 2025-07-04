<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Attendance_change;
use App\Models\User;
use App\Models\WorkBreak;
use App\Models\WorkBreak_change;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminApprovalTest extends TestCase
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

    public function test_admin_pending_list()
    {
        $users = User::whereIn('email', [
            'user1@example.com',
            'user2@example.com',
        ])->get();

        foreach ( $users as $user) {
            $user->markEmailAsVerified();

            $attendance = Attendance::where('user_id', $user->id)
                ->whereMonth('work_date', 6)
                ->first();
            $workBreak = $attendance->WorkBreaks()->first();

            $postData = [
                'attendance_id' => $attendance->id,
                'new_clock_in' => '09:00',
                'new_clock_out' => '18:00',
                'remarks' => 'テストの変更申請',
                'breaks' => [
                    [
                        'start' => '12:00',
                        'end' => '13:00',
                    ],   
                ],
                'work_break_id' => [$workBreak->id],
            ];

            $this->actingAs($user);
            $response = $this->post("/attendance/{$attendance->id}", $postData);
        }

        $admin = \App\Models\Admin::where('email', 'admin@gmail.com')->first();
        $this->actingAs($admin, 'admin');

        $listResponse = $this->get('/stamp_correction_request/list');
        $listResponse->assertStatus(200);

        $listResponse->assertSeeText('承認待ち');

        foreach ($users as $user) {
            $listResponse->assertSeeText($user->name);
            $listResponse->assertSeeText('テストの変更申請');
        }
    }

    public function test_admin_approved_list()
    {
        $users = User::whereIn('email', [
            'user1@example.com',
            'user2@example.com',
        ])->get();

        foreach ($users as $user) {
            $user->markEmailAsVerified();

            $attendance = Attendance::where('user_id', $user->id)
                ->whereMonth('work_date', 6)
                ->first();
            $workBreak = $attendance->WorkBreaks()->first();

            $postData = [
                'attendance_id' => $attendance->id,
                'new_clock_in' => '09:00',
                'new_clock_out' => '18:00',
                'remarks' => 'テストの承認済み申請',
                'breaks' => [
                    [
                        'start' => '12:00',
                        'end' => '13:00',
                    ],
                ],
                'work_break_id' => [$workBreak->id],
            ];

            $this->actingAs($user);
            $response = $this->post("/attendance/{$attendance->id}", $postData);

            $attendanceChange = Attendance_change::where('user_id', $user->id)
                ->latest()
                ->first();
            $attendanceChange->update(['status' => 'approved']);

            $workBreakChange = WorkBreak_change::where('attendance_change_id', $attendanceChange->id)->first();
            $workBreakChange->update(['status' => 'approved']);
        }

        $admin = \App\Models\Admin::where('email', 'admin@gmail.com')->first();
        $this->actingAs($admin, 'admin');

        $listResponse = $this->get('/stamp_correction_request/list?tab=approved');
        $listResponse->assertStatus(200);

        $listResponse->assertSeeText('承認済み');

        foreach ($users as $user) {
            $listResponse->assertSeeText($user->name);
            $listResponse->assertSeeText('テストの承認済み申請');
        }
    }

    public function test_admin_pending_detail()
    {
        $user = User::where('email', 'user1@example.com')->first();
        $user->markEmailAsVerified();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereMonth('work_date', 6)
            ->first();
        $workBreak = $attendance->WorkBreaks()->first();

        $postData = [
            'attendance_id' => $attendance->id,
            'new_clock_in' => '09:00',
            'new_clock_out' => '18:00',
            'remarks' => 'テストの詳細確認申請',
            'breaks' => [
                [
                    'start' => '12:00',
                    'end' => '13:00',
                ],
            ],
            'work_break_id' => [$workBreak->id],
        ];
        $this->actingAs($user);
        $response = $this->post("/attendance/{$attendance->id}", $postData);

        $admin = \App\Models\Admin::where('email', 'admin@gmail.com')->first();
        $this->actingAs($admin, 'admin');

        $attendanceChange = Attendance_change::where('user_id', $user->id)
            ->latest()
            ->first();
        $workBreakChange = WorkBreak_change::where('attendance_change_id', $attendanceChange->id)->first();

        $response = $this->get("/stamp_correction_request/approve/{$attendanceChange->id}");
        $response->assertStatus(200);

        $response->assertSeeText($user->name);
        $response->assertSeeText('テストの詳細確認申請');
        $response->assertSeeText('09:00');
        $response->assertSeeText('18:00');
        $response->assertSeeText('12:00');
        $response->assertSeeText('13:00');
    }

    public function test_admin_approved_process()
    {
        $user = User::where('email', 'user1@example.com')->first();
        $user->markEmailAsVerified();

        $attendance = Attendance::with('workBreaks')
            ->where('user_id', $user->id)
            ->whereMonth('work_date', 6)
            ->first();

        $workBreak = $attendance->workBreaks()->first();

        $postData = [
            'attendance_id' => $attendance->id,
            'new_clock_in' => '09:00',
            'new_clock_out' => '18:00',
            'remarks' => 'テストの詳細確認申請',
            'breaks' => [
                [
                    'start' => '12:00',
                    'end' => '13:00',
                ],
            ],
            'work_break_id' => [$workBreak->id],
        ];

        $this->actingAs($user);
        $response = $this->post("/attendance/{$attendance->id}", $postData);
        $response->assertSessionHasNoErrors();

        $attendanceChange = Attendance_change::where('user_id', $user->id)->latest()->first();
        $this->assertDatabaseHas('attendance_changes', [
            'id' => $attendanceChange->id,
            'status' => 'pending',
        ]);

        $workBreakChange = WorkBreak_change::where('attendance_change_id', $attendanceChange->id)->first();
        $this->assertDatabaseHas('work_break_changes', [
            'id' => $workBreakChange->id,
            'status' => 'pending',
        ]);

        $admin = \App\Models\Admin::where('email', 'admin@gmail.com')->first();
        $this->actingAs($admin, 'admin');

        $response = $this->post("/stamp_correction_request/approve/{$attendanceChange->id}");
        $response->assertRedirect('/stamp_correction_request/list');

        $this->assertDatabaseHas('attendance_changes', [
            'id' => $attendanceChange->id,
            'status' => 'approved',
            'admin_id' => $admin->id,
        ]);

        $this->assertDatabaseHas('work_break_changes', [
            'id' => $workBreakChange->id,
            'attendance_change_id' => $attendanceChange->id,
            'status' => 'approved',
            'admin_id' => $admin->id,
        ]);

        $attendance->refresh();
        $this->assertEquals('09:00:00', $attendance->clock_in);
        $this->assertEquals('18:00:00', $attendance->clock_out);

        $updateWorkBreak = WorkBreak::find($workBreak->id);
        $this->assertEquals('12:00:00', $updateWorkBreak->break_start);
        $this->assertEquals('13:00:00', $updateWorkBreak->break_end);
    }
}
