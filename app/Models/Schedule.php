<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'lop_id', 'school_id', 'days_of_week', 'start_time', 'end_time', 'location',
    ];

    protected function casts(): array
    {
        return [
            'days_of_week' => 'array',
        ];
    }

    public function lop()
    {
        return $this->belongsTo(Lop::class, 'lop_id');
    }
}
