<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'clock_out'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function workBreaks()
    {
        return $this->hasMany(WorkBreak::class);
    }

    public function attendanceChange()
    {
        return $this->hasOne(Attendance_change::class);
    }
}
