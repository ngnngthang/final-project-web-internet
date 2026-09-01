<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinalGrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'lop_id', 'school_id', 'academic_year',
        'final_score', 'is_complete', 'pass_status', 'published_at', 'published_by',
    ];

    protected function casts(): array
    {
        return [
            'final_score' => 'decimal:2',
            'is_complete' => 'boolean',
            'published_at' => 'datetime',
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
}
