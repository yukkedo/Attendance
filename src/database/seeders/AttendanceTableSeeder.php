<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $year = 2025;
        $months = [1, 2, 3, 4, 5, 6, 7];
        $userIds = range(1, 5);

        foreach ($userIds as $userId) {
            // 各ユーザーに対して処理
            foreach ($months as $month) {
                // 各月に対して処理
                // 勤務日数カウントと日付のループ
                $date = Carbon::create($year, $month, 1);
                $workDaysCount = 0;

                while ($workDaysCount < 20) {
                    if(!$date->isWeekend()) {
                        $clockIn = $date->copy()->setTime(9, 0)->addMinutes(rand(0, 30));
                        $clockOut = $date->copy()->setTime(17, 30)->addMinutes(rand(0, 60));

                        $attendanceId = DB::table('attendances')->insertGetId([
                            'user_id' => $userId,
                            'work_date' => $date->toDateString(),
                            'clock_in' => $clockIn,
                            'clock_out' => $clockOut,
                        ]);

                        $breakCount = rand(1, 2);
                        $breakStart = $clockIn->copy()->addHours(2);

                        for ($i = 0; $i <$breakCount; $i++) {
                            $start = $breakStart->copy()->addMinutes(rand(20, 60));
                            $end = $start->copy()->addMinutes(rand(10, 40));


                            if ($end->greaterThan($clockOut)) {
                                break;
                            }

                            DB::table('work_breaks')->insert([
                                'attendance_id' => $attendanceId,
                                'break_start' => $start,
                                'break_end' => $end,
                            ]);

                            $breakStart = $end->copy()->addMinutes(30);
                        }
                        $workDaysCount++;
                    }
                    $date->addDay();
                }
            }
        }
    }
}
