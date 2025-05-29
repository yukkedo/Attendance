<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance_change extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'new_clock_in',
        'new_clock_out',
        'remarks',
        'status',
        'admin_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function workBreakChanges()
    {
        return $this->hasMany(WorkBreak_change::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
