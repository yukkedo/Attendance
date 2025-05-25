<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkBreak_change extends Model
{
    use HasFactory;

    public function work_break()
    {
        return $this->belongsTo(WorkBreak::class);
    }

    public function attendanceChange()
    {
        return $this->belongsTo(Attendance_change::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
