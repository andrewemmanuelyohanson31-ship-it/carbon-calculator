<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Calculation extends Model
{
    use HasUuids;

    protected $fillable = ['visitor_id', 'household_size', 'inputs', 'breakdown', 'total_kg'];

    protected $hidden = ['visitor_id'];

    protected function casts(): array
    {
        return [
            'inputs' => 'array',
            'breakdown' => 'array',
            'total_kg' => 'float',
        ];
    }
}
