<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lop extends Model
{
    use HasFactory;

    protected $table = 'lop';

    protected $fillable = [
        'khoi_id', 'school_id', 'teacher_id', 'name', 'max_capacity', 'current_enrollment', 'status',
    ];

    public function khoi()
    {
        return $this->belongsTo(Khoi::class, 'khoi_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'lop_id');
    }

    public function schedule()
    {
        return $this->hasOne(Schedule::class, 'lop_id');
    }

    public function scores()
    {
        return $this->hasMany(Score::class, 'lop_id');
    }

    public function finalGrades()
    {
        return $this->hasMany(FinalGrade::class, 'lop_id');
    }
}
