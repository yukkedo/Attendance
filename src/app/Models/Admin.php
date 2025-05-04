<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;

    public function attendance_change()
    {
        return $this->hasMany(Attendance_change::class);
    }

    public function work_break_change()
    {
        return $this->hasMany(WorkBreak_change::class);
    }
}
