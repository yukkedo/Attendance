<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Attendance_change;
use Illuminate\Http\Request;

class StampCorrectionRequestController extends Controller
{
    public function applyList(Request $request)
    {
        $tab = $request->query('tab', 'pending');

        if (auth('admin')->check()) {
            $query = Attendance_change::with(['user', 'attendance'])
                ->where('status', $tab);
        } elseif (auth('web')->check()) {
            $userId = auth('web')->id();

            $query = Attendance_change::with(['user', 'attendance'])
                ->where('status', $tab)
                ->where('user_id', $userId);
        }
        $changes = $query->get();

        return view('application_list', compact(
            'tab',
            'changes'
        ));
    }
}
