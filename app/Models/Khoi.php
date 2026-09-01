<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Khoi extends Model
{
    use HasFactory;

    protected $table = 'khoi';

    protected $fillable = [
        'school_id', 'name', 'academic_year', 'is_active', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function lop()
    {
        return $this->hasMany(Lop::class);
    }
}
