<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'lop_id', 'assessment_type_id', 'teacher_id', 'school_id',
        'numeric_score', 'notes', 'is_verified', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'numeric_score' => 'decimal:2',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function lop()
    {
        return $this->belongsTo(Lop::class, 'lop_id');
    }

    public function assessmentType()
    {
        return $this->belongsTo(AssessmentType::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
